<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

final class DatabaseUser implements ModelInterface
{
    public function __construct(
        public readonly int $id,
        public readonly int $dbId,
        public readonly string $user,
        public readonly string $perm,
        public readonly string $userGrant,
        public readonly ?string $info,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: (int) $data['id'],
            dbId: (int) $data['db_id'],
            user: (string) $data['user'],
            perm: (string) $data['perm'],
            userGrant: (string) $data['user_grant'],
            info: isset($data['info']) ? (string) $data['info'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'db_id' => $this->dbId,
            'user' => $this->user,
            'perm' => $this->perm,
            'user_grant' => $this->userGrant,
            'info' => $this->info,
        ];
    }
}
