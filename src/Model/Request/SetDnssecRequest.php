<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model\Request;

use JanMuran\SpeedwebApiSdk\Model\ModelInterface;

final class SetDnssecRequest implements ModelInterface
{
    public function __construct(
        public readonly bool $enabled,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(enabled: (bool) $data['enabled']);
    }

    public function toArray(): array
    {
        return ['enabled' => $this->enabled];
    }
}
