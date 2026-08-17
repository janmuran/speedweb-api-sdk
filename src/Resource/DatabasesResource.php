<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\Database;
use JanMuran\SpeedwebApiSdk\Model\DatabaseUser;
use JanMuran\SpeedwebApiSdk\Model\Request\ChangeDatabaseUserPasswordRequest;

/**
 * `Databases` tag: domain databases and database users.
 */
final class DatabasesResource extends AbstractResource
{
    /**
     * @return Database[]
     */
    public function list(int $domainId): array
    {
        $data = $this->http->request('GET', "/api/v1/domains/{$domainId}/databases");

        return array_map(
            static fn (array $item): Database => Database::fromArray($item),
            $data['data'] ?? [],
        );
    }

    /**
     * @return DatabaseUser[]
     */
    public function listUsers(int $domainId, int $databaseId): array
    {
        $data = $this->http->request('GET', "/api/v1/domains/{$domainId}/databases/{$databaseId}/users");

        return array_map(
            static fn (array $item): DatabaseUser => DatabaseUser::fromArray($item),
            $data['data'] ?? [],
        );
    }

    public function changeUserPassword(
        int $domainId,
        int $databaseId,
        int $userId,
        ChangeDatabaseUserPasswordRequest $request,
    ): void {
        $this->http->request(
            'PATCH',
            "/api/v1/domains/{$domainId}/databases/{$databaseId}/users/{$userId}/password",
            body: $request->toArray(),
        );
    }
}
