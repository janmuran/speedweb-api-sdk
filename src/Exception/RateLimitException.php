<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Exception;

use Psr\Http\Message\RequestInterface;

/**
 * Thrown on HTTP 429 (rate limit exceeded).
 */
final class RateLimitException extends ApiException
{
    /**
     * @param array<string, mixed>|null $responseBody
     */
    public function __construct(
        string $message,
        ?int $statusCode,
        ?array $responseBody,
        ?RequestInterface $request,
        private readonly ?int $retryAfterSeconds = null,
    ) {
        parent::__construct($message, $statusCode, $responseBody, $request);
    }

    public function getRetryAfterSeconds(): ?int
    {
        return $this->retryAfterSeconds;
    }
}
