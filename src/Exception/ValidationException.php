<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Exception;

/**
 * Thrown on HTTP 422 (server-side validation error).
 */
final class ValidationException extends ApiException
{
    /**
     * @return array<string, mixed>
     */
    public function getErrors(): array
    {
        $body = $this->getResponseBody() ?? [];

        return is_array($body['errors'] ?? null) ? $body['errors'] : [];
    }
}
