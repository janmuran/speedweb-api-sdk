<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model\Request;

use InvalidArgumentException;
use JanMuran\SpeedwebApiSdk\Model\ModelInterface;

final class UpdateDnsRecordRequest implements ModelInterface
{
    public function __construct(
        public readonly string $type,
        public readonly string $value,
        public readonly int $ttl,
        public readonly ?string $name = null,
        public readonly ?string $note = null,
    ) {
        if ($ttl < 300 || $ttl > 86400) {
            throw new InvalidArgumentException('ttl must be between 300 and 86400 seconds.');
        }
    }

    public static function fromArray(array $data): static
    {
        return new self(
            type: (string) $data['type'],
            value: (string) $data['value'],
            ttl: (int) $data['ttl'],
            name: isset($data['name']) ? (string) $data['name'] : null,
            note: isset($data['note']) ? (string) $data['note'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'name' => $this->name,
                'type' => $this->type,
                'value' => $this->value,
                'ttl' => $this->ttl,
                'note' => $this->note,
            ],
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
