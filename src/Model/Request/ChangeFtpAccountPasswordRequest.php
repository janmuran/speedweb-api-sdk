<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model\Request;

use InvalidArgumentException;
use JanMuran\SpeedwebApiSdk\Model\ModelInterface;

final class ChangeFtpAccountPasswordRequest implements ModelInterface
{
    public function __construct(
        public readonly string $password,
    ) {
        if (mb_strlen($password) < 6) {
            throw new InvalidArgumentException('password must be at least 6 characters long.');
        }
    }

    public static function fromArray(array $data): static
    {
        return new self(password: (string) $data['password']);
    }

    public function toArray(): array
    {
        return ['password' => $this->password];
    }
}
