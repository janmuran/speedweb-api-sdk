<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

final class FtpAccount implements ModelInterface
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $username,
        public readonly string $dir,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            username: (string) ($data['username'] ?? $data['name']),
            dir: (string) $data['dir'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'dir' => $this->dir,
        ];
    }
}
