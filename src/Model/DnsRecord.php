<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

final class DnsRecord implements ModelInterface
{
    public function __construct(
        public readonly int $id,
        public readonly int $domainId,
        public readonly string $name,
        public readonly int $ttl,
        public readonly string $type,
        public readonly string $value,
        public readonly ?string $memo = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: (int) $data['id'],
            domainId: (int) $data['domain_id'],
            name: (string) $data['name'],
            ttl: (int) $data['ttl'],
            type: (string) $data['type'],
            value: (string) $data['value'],
            memo: isset($data['memo']) ? (string) $data['memo'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'domain_id' => $this->domainId,
            'name' => $this->name,
            'ttl' => $this->ttl,
            'type' => $this->type,
            'value' => $this->value,
            'memo' => $this->memo,
        ];
    }
}
