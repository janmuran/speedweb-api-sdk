<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model\Request;

use InvalidArgumentException;
use JanMuran\SpeedwebApiSdk\Model\ModelInterface;

final class AssignSubuserDomainRequest implements ModelInterface
{
    private const ALLOWED_PRIVILEGES = ['web', 'ftp', 'email', 'db', 'dns', 'edit'];

    /**
     * @param string[]|null $privileges Enabled modules; null or empty = all.
     */
    public function __construct(
        public readonly ?string $domain = null,
        public readonly ?int $domainId = null,
        public readonly ?array $privileges = null,
    ) {
        if ($domain === null && $domainId === null) {
            throw new InvalidArgumentException('either domain or domainId must be provided.');
        }

        foreach ($privileges ?? [] as $privilege) {
            if (!in_array($privilege, self::ALLOWED_PRIVILEGES, true)) {
                throw new InvalidArgumentException('privileges may only contain: ' . implode(', ', self::ALLOWED_PRIVILEGES) . '.');
            }
        }
    }

    public static function fromArray(array $data): static
    {
        return new self(
            domain: isset($data['domain']) ? (string) $data['domain'] : null,
            domainId: isset($data['domain_id']) ? (int) $data['domain_id'] : null,
            privileges: $data['privileges'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'domain' => $this->domain,
                'domain_id' => $this->domainId,
                'privileges' => $this->privileges,
            ],
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
