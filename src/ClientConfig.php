<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk;

use InvalidArgumentException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * Immutable configuration for {@see ApiClient}. Construct directly or via
 * {@see self::forHost()} to pick one of the branded hosts from the
 * OpenAPI `servers` block.
 */
final class ClientConfig
{
    public const DEFAULT_HOST = 'api.speedweb.sk';

    /** @var string[] */
    public const ALLOWED_HOSTS = [
        'api.speedweb.sk',
        'api.smartweb.eu',
        'api.webcentrum.sk',
        'api.inet.sk',
        'api.mam.sk',
        'api.cmcdata.sk',
        'api.nshosting.eu',
    ];

    /**
     * @param int[] $retryableStatusCodes
     */
    public function __construct(
        public readonly string $apiKey,
        public readonly string $baseUri = 'https://' . self::DEFAULT_HOST,
        public readonly float $timeout = 10.0,
        public readonly float $connectTimeout = 5.0,
        public readonly int $maxRetries = 2,
        public readonly int $retryBaseDelayMs = 200,
        public readonly float $retryMultiplier = 2.0,
        public readonly int $retryMaxDelayMs = 5000,
        public readonly array $retryableStatusCodes = [429, 500, 502, 503, 504],
        public readonly bool $loggingEnabled = false,
        public readonly string $logLevel = LogLevel::INFO,
        public readonly ?LoggerInterface $logger = null,
        public readonly ?ClientInterface $httpClient = null,
        public readonly ?RequestFactoryInterface $requestFactory = null,
        public readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        if (trim($apiKey) === '') {
            throw new InvalidArgumentException('API key must not be empty.');
        }

        if ($maxRetries < 0) {
            throw new InvalidArgumentException('maxRetries must not be negative.');
        }
    }

    /**
     * @param array<string, mixed> $options Any other named {@see self::__construct()} argument.
     */
    public static function forHost(string $apiKey, string $host = self::DEFAULT_HOST, array $options = []): self
    {
        if (!in_array($host, self::ALLOWED_HOSTS, true)) {
            throw new InvalidArgumentException(sprintf(
                'Unknown host "%s". Allowed hosts: %s',
                $host,
                implode(', ', self::ALLOWED_HOSTS),
            ));
        }

        unset($options['apiKey'], $options['baseUri']);

        return new self(...array_merge(['apiKey' => $apiKey, 'baseUri' => 'https://' . $host], $options));
    }

    /**
     * Resolves the effective logger: a {@see NullLogger} whenever logging is
     * disabled, regardless of whether a logger was supplied, so that
     * turning `loggingEnabled` off always fully silences the SDK.
     */
    public function getLogger(): LoggerInterface
    {
        if (!$this->loggingEnabled) {
            return new NullLogger();
        }

        return $this->logger ?? new NullLogger();
    }
}
