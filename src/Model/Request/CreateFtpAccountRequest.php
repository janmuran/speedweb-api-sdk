<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model\Request;

use InvalidArgumentException;
use JanMuran\SpeedwebApiSdk\Model\ModelInterface;

final class CreateFtpAccountRequest implements ModelInterface
{
    public function __construct(
        public readonly string $name,
        public readonly string $password,
        public readonly string $dir,
    ) {
        if (mb_strlen($password) < 6) {
            throw new InvalidArgumentException('password must be at least 6 characters long.');
        }
    }

    public static function fromArray(array $data): static
    {
        return new self(
            name: (string) $data['name'],
            password: (string) $data['password'],
            dir: (string) $data['dir'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'password' => $this->password,
            'dir' => $this->dir,
        ];
    }
}
