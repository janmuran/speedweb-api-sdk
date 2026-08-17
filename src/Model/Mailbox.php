<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

final class Mailbox implements ModelInterface
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $mailroute,
        public readonly bool $mailDelivery,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            mailroute: isset($data['mailroute']) ? (string) $data['mailroute'] : null,
            mailDelivery: (bool) ($data['mailDelivery'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mailroute' => $this->mailroute,
            'mailDelivery' => $this->mailDelivery,
        ];
    }
}
