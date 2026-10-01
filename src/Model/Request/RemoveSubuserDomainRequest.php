<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model\Request;

use InvalidArgumentException;
use JanMuran\SpeedwebApiSdk\Model\ModelInterface;

final class RemoveSubuserDomainRequest implements ModelInterface
{
    public function __construct(
        public readonly ?string $domain = null,
        public readonly ?int $domainId = null,
    ) {
        if ($domain === null && $domainId === null) {
            throw new InvalidArgumentException('either domain or domainId must be provided.');
        }
    }

    public static function fromArray(array $data): static
    {
        return new self(
            domain: isset($data['domain']) ? (string) $data['domain'] : null,
            domainId: isset($data['domain_id']) ? (int) $data['domain_id'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'domain' => $this->domain,
                'domain_id' => $this->domainId,
            ],
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
