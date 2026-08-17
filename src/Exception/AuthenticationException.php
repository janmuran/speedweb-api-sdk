<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Exception;

/**
 * Thrown on HTTP 401 (invalid or missing API key).
 */
final class AuthenticationException extends ApiException
{
}
