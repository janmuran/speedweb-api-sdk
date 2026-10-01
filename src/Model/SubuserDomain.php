<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

/**
 * Domain assigned to a subuser via acl_domains, including module privileges.
 */
final class SubuserDomain implements ModelInterface
{
    public function __construct(
        public readonly int $id,
        public readonly int $domainId,
        public readonly string $domain,
        public readonly int $web,
        public readonly int $ftp,
        public readonly int $email,
        public readonly int $db,
        public readonly int $dns,
        public readonly int $edit,
        public readonly string $permModules,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: (int) $data['id'],
            domainId: (int) $data['domain_id'],
            domain: (string) $data['domain'],
            web: (int) $data['web'],
            ftp: (int) $data['ftp'],
            email: (int) $data['email'],
            db: (int) $data['db'],
            dns: (int) $data['dns'],
            edit: (int) $data['edit'],
            permModules: (string) $data['perm_modules'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'domain_id' => $this->domainId,
            'domain' => $this->domain,
            'web' => $this->web,
            'ftp' => $this->ftp,
            'email' => $this->email,
            'db' => $this->db,
            'dns' => $this->dns,
            'edit' => $this->edit,
            'perm_modules' => $this->permModules,
        ];
    }
}
