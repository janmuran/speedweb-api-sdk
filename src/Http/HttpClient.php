<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use JanMuran\SpeedwebApiSdk\ClientConfig;
use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Exception\AuthenticationException;
use JanMuran\SpeedwebApiSdk\Exception\NetworkException;
use JanMuran\SpeedwebApiSdk\Exception\RateLimitException;
use JanMuran\SpeedwebApiSdk\Exception\ValidationException;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Low-level HTTP layer built on top of PSR-18/PSR-17. Handles base URI
 * resolution, bearer authentication, JSON (de)serialization, exponential
 * backoff retries and PSR-3 logging. {@see Resource\AbstractResource}
 * subclasses are the only intended callers.
 */
final class HttpClient
{
    private const SENSITIVE_HEADERS = ['authorization', 'x-api-key'];

    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private LoggerInterface $logger;

    /** @var callable(int): void */
    private $sleeper;

    public function __construct(
        private readonly ClientConfig $config,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?callable $sleeper = null,
    ) {
        $this->httpClient = $httpClient ?? $config->httpClient ?? new GuzzleClient([
            'timeout' => $config->timeout,
            'connect_timeout' => $config->connectTimeout,
        ]);
        $this->requestFactory = $requestFactory ?? $config->requestFactory ?? new HttpFactory();
        $this->streamFactory = $streamFactory ?? $config->streamFactory ?? new HttpFactory();
        $this->logger = $config->getLogger();
        $this->sleeper = $sleeper ?? static function (int $milliseconds): void {
            usleep($milliseconds * 1000);
        };
    }

    /**
     * @param array<string, scalar|null> $query
     * @param array<string, mixed>|null $body
     * @param array<string, string> $headers Extra request headers (e.g. Idempotency-Key), merged in last.
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, array $query = [], ?array $body = null, array $headers = []): array
    {
        $uri = rtrim($this->config->baseUri, '/') . '/' . ltrim($path, '/');

        $query = array_filter($query, static fn (mixed $value): bool => $value !== null);
        if ($query !== []) {
            $uri .= '?' . http_build_query($query);
        }

        $request = $this->requestFactory->createRequest($method, $uri)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer ' . $this->config->apiKey);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null) {
            try {
                $json = json_encode($body, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new ApiException('Failed to encode request body as JSON: ' . $e->getMessage(), previous: $e);
            }

            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream($json));
        }

        return $this->sendWithRetry($request);
    }

    /**
     * @return array<string, mixed>
     */
    private function sendWithRetry(RequestInterface $request): array
    {
        $delayMs = $this->config->retryBaseDelayMs;
        $lastException = null;
        $totalAttempts = $this->config->maxRetries + 1;

        for ($attempt = 1; $attempt <= $totalAttempts; $attempt++) {
            $this->logger->log($this->config->logLevel, sprintf(
                '[speedweb-sdk] -> %s %s (attempt %d/%d)',
                $request->getMethod(),
                (string) $request->getUri(),
                $attempt,
                $totalAttempts,
            ), ['headers' => $this->maskHeaders($request->getHeaders())]);

            $startedAt = microtime(true);

            try {
                $response = $this->httpClient->sendRequest($request);
            } catch (ClientExceptionInterface $e) {
                $lastException = $e;
                $this->logger->log($this->config->logLevel, sprintf(
                    '[speedweb-sdk] network error on attempt %d/%d: %s',
                    $attempt,
                    $totalAttempts,
                    $e->getMessage(),
                ));

                if ($attempt >= $totalAttempts) {
                    break;
                }

                $this->wait($delayMs);
                $delayMs = $this->nextDelay($delayMs);
                continue;
            }

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $status = $response->getStatusCode();

            $this->logger->log($this->config->logLevel, sprintf(
                '[speedweb-sdk] <- %s %s => %d (%d ms, attempt %d/%d)',
                $request->getMethod(),
                (string) $request->getUri(),
                $status,
                $durationMs,
                $attempt,
                $totalAttempts,
            ));

            $isRetryable = in_array($status, $this->config->retryableStatusCodes, true);
            if ($isRetryable && $attempt < $totalAttempts) {
                $this->wait($delayMs);
                $delayMs = $this->nextDelay($delayMs);
                continue;
            }

            return $this->handleResponse($request, $response);
        }

        throw new NetworkException(
            sprintf(
                'Request to %s failed after %d attempt(s)%s',
                (string) $request->getUri(),
                $totalAttempts,
                $lastException !== null ? ': ' . $lastException->getMessage() : '',
            ),
            $request,
            $lastException,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function handleResponse(RequestInterface $request, ResponseInterface $response): array
    {
        $status = $response->getStatusCode();
        $rawBody = (string) $response->getBody();
        $decoded = $rawBody === '' ? [] : json_decode($rawBody, true);
        $decoded = is_array($decoded) ? $decoded : [];

        if ($status >= 200 && $status < 300) {
            return $decoded;
        }

        $message = is_string($decoded['message'] ?? null)
            ? $decoded['message']
            : sprintf('Speedweb API request failed with status %d', $status);

        throw match (true) {
            $status === 401 => new AuthenticationException($message, $status, $decoded, $request),
            $status === 422 => new ValidationException($message, $status, $decoded, $request),
            $status === 429 => new RateLimitException($message, $status, $decoded, $request, $this->parseRetryAfter($response)),
            default => new ApiException($message, $status, $decoded, $request),
        };
    }

    private function parseRetryAfter(ResponseInterface $response): ?int
    {
        $value = $response->getHeaderLine('Retry-After');

        return $value === '' ? null : (int) $value;
    }

    private function nextDelay(int $currentDelayMs): int
    {
        return min((int) round($currentDelayMs * $this->config->retryMultiplier), $this->config->retryMaxDelayMs);
    }

    private function wait(int $milliseconds): void
    {
        ($this->sleeper)($milliseconds);
    }

    /**
     * @param array<string, string[]> $headers
     * @return array<string, string[]>
     */
    private function maskHeaders(array $headers): array
    {
        foreach ($headers as $name => $values) {
            if (in_array(strtolower($name), self::SENSITIVE_HEADERS, true)) {
                $headers[$name] = array_map(
                    static fn (string $value): string => strlen($value) > 8 ? substr($value, 0, 8) . '***' : '***',
                    $values,
                );
            }
        }

        return $headers;
    }
}
