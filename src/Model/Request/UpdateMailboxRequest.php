<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model\Request;

use InvalidArgumentException;
use JanMuran\SpeedwebApiSdk\Model\ModelInterface;

final class UpdateMailboxRequest implements ModelInterface
{
    public function __construct(
        public readonly ?string $password = null,
        public readonly ?int $quota = null,
    ) {
        if ($password !== null && mb_strlen($password) < 6) {
            throw new InvalidArgumentException('password must be at least 6 characters long.');
        }

        if ($password === null && $quota === null) {
            throw new InvalidArgumentException('at least one of password or quota must be provided.');
        }
    }

    public static function fromArray(array $data): static
    {
        return new self(
            password: isset($data['password']) ? (string) $data['password'] : null,
            quota: isset($data['quota']) ? (int) $data['quota'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'password' => $this->password,
                'quota' => $this->quota,
            ],
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
