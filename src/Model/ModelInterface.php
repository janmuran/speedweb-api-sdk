<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

interface ModelInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
