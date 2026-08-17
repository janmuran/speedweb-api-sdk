<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model\Request;

use InvalidArgumentException;
use JanMuran\SpeedwebApiSdk\Model\ModelInterface;

final class CreateMailboxRequest implements ModelInterface
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly ?int $quota = null,
    ) {
        if (mb_strlen($password) < 6) {
            throw new InvalidArgumentException('password must be at least 6 characters long.');
        }
    }

    public static function fromArray(array $data): static
    {
        return new self(
            email: (string) $data['email'],
            password: (string) $data['password'],
            quota: isset($data['quota']) ? (int) $data['quota'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'email' => $this->email,
                'password' => $this->password,
                'quota' => $this->quota,
            ],
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
