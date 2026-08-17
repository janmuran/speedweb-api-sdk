<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

final class Domain implements ModelInterface
{
    public function __construct(
        public readonly int $id,
        public readonly string $domain,
        public readonly string $server,
        public readonly string $mailserver,
        public readonly int $emailUsageMb,
        public readonly int $ftpUsageMb,
        public readonly int $dbUsageMb,
        public readonly ?string $quotaUpdated,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: (int) $data['id'],
            domain: (string) $data['domain'],
            server: (string) $data['server'],
            mailserver: (string) $data['mailserver'],
            emailUsageMb: (int) ($data['email'] ?? 0),
            ftpUsageMb: (int) ($data['ftp'] ?? 0),
            dbUsageMb: (int) ($data['db'] ?? 0),
            quotaUpdated: isset($data['quota_updated']) ? (string) $data['quota_updated'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'domain' => $this->domain,
            'server' => $this->server,
            'mailserver' => $this->mailserver,
            'email' => $this->emailUsageMb,
            'ftp' => $this->ftpUsageMb,
            'db' => $this->dbUsageMb,
            'quota_updated' => $this->quotaUpdated,
        ];
    }
}
