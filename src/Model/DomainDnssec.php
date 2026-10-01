<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

final class DomainDnssec implements ModelInterface
{
    public function __construct(
        public readonly bool $enabled,
        public readonly ?int $status,
        public readonly ?string $lastSigned,
        public readonly ?string $dsset,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            enabled: (bool) ($data['enabled'] ?? false),
            status: isset($data['status']) ? (int) $data['status'] : null,
            lastSigned: isset($data['last_signed']) ? (string) $data['last_signed'] : null,
            dsset: isset($data['dsset']) ? (string) $data['dsset'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'status' => $this->status,
            'last_signed' => $this->lastSigned,
            'dsset' => $this->dsset,
        ];
    }
}
