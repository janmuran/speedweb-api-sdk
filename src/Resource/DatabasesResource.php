<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\Database;
use JanMuran\SpeedwebApiSdk\Model\DatabaseUser;
use JanMuran\SpeedwebApiSdk\Model\PaginatedCollection;
use JanMuran\SpeedwebApiSdk\Model\Request\ChangeDatabaseUserPasswordRequest;

/**
 * `Databases` tag: domain databases and database users.
 */
final class DatabasesResource extends AbstractResource
{
    /**
     * @return PaginatedCollection<Database>
     */
    public function list(int $domainId, ?int $page = null, ?int $perPage = null): PaginatedCollection
    {
        $data = $this->http->request('GET', "/api/v1/domains/{$domainId}/databases", [
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return PaginatedCollection::fromArray($data, Database::class);
    }

    /**
     * @return PaginatedCollection<DatabaseUser>
     */
    public function listUsers(int $domainId, int $databaseId, ?int $page = null, ?int $perPage = null): PaginatedCollection
    {
        $data = $this->http->request('GET', "/api/v1/domains/{$domainId}/databases/{$databaseId}/users", [
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return PaginatedCollection::fromArray($data, DatabaseUser::class);
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
