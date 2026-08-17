<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Exception;

use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Throwable;

class ApiException extends RuntimeException
{
    /**
     * @param array<string, mixed>|null $responseBody
     */
    public function __construct(
        string $message,
        private readonly ?int $statusCode = null,
        private readonly ?array $responseBody = null,
        private readonly ?RequestInterface $request = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode ?? 0, $previous);
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }

    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }
}
