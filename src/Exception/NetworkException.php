<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Exception;

use Psr\Http\Message\RequestInterface;
use Throwable;

/**
 * Thrown when the underlying PSR-18 client fails to complete a request
 * (connection refused, timeout, DNS failure, ...) even after retries.
 */
final class NetworkException extends ApiException
{
    public function __construct(string $message, ?RequestInterface $request = null, ?Throwable $previous = null)
    {
        parent::__construct($message, null, null, $request, $previous);
    }
}
