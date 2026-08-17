<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model\Request;

use InvalidArgumentException;
use JanMuran\SpeedwebApiSdk\Model\ModelInterface;

final class ChangeDatabaseUserPasswordRequest implements ModelInterface
{
    public function __construct(
        public readonly string $password,
    ) {
        $length = mb_strlen($password);
        if ($length < 6 || $length > 100) {
            throw new InvalidArgumentException('password must be between 6 and 100 characters long.');
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
