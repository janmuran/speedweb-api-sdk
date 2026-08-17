<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

final class Subuser implements ModelInterface
{
    public function __construct(
        public readonly int $id,
        public readonly string $login,
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: (int) $data['id'],
            login: (string) $data['login'],
            name: isset($data['name']) ? (string) $data['name'] : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
}
