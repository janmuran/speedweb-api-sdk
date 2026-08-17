<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

final class Database implements ModelInterface
{
    /**
     * @param DatabaseUser[] $users
     */
    public function __construct(
        public readonly int $id,
        public readonly int $domainId,
        public readonly string $nazov,
        public readonly string $type,
        public readonly ?int $quota,
        public readonly ?string $memo,
        public readonly ?int $serverId,
        public readonly array $users,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: (int) $data['id'],
            domainId: (int) $data['domain_id'],
            nazov: (string) $data['nazov'],
            type: (string) $data['type'],
            quota: isset($data['quota']) ? (int) $data['quota'] : null,
            memo: isset($data['memo']) ? (string) $data['memo'] : null,
            serverId: isset($data['server_id']) ? (int) $data['server_id'] : null,
            users: array_map(
                static fn (array $user): DatabaseUser => DatabaseUser::fromArray($user),
                $data['users'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'domain_id' => $this->domainId,
            'nazov' => $this->nazov,
            'type' => $this->type,
            'quota' => $this->quota,
            'memo' => $this->memo,
            'server_id' => $this->serverId,
            'users' => array_map(static fn (DatabaseUser $user): array => $user->toArray(), $this->users),
        ];
    }
}
