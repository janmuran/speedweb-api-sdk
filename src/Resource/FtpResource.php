<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\FtpAccount;
use JanMuran\SpeedwebApiSdk\Model\PaginatedCollection;
use JanMuran\SpeedwebApiSdk\Model\Request\ChangeFtpAccountPasswordRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateFtpAccountRequest;

/**
 * `FTP` tag: domain FTP accounts.
 */
final class FtpResource extends AbstractResource
{
    /**
     * @return PaginatedCollection<FtpAccount>
     */
    public function list(int $domainId, ?int $page = null, ?int $perPage = null): PaginatedCollection
    {
        $data = $this->http->request('GET', "/api/v1/domains/{$domainId}/ftp-accounts", [
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return PaginatedCollection::fromArray($data, FtpAccount::class);
    }

    public function create(int $domainId, CreateFtpAccountRequest $request, ?string $idempotencyKey = null): FtpAccount
    {
        $data = $this->http->request(
            'POST',
            "/api/v1/domains/{$domainId}/ftp-accounts",
            body: $request->toArray(),
            headers: $idempotencyKey !== null ? ['Idempotency-Key' => $idempotencyKey] : [],
        );

        return FtpAccount::fromArray($data['data'] ?? []);
    }

    public function changePassword(int $domainId, int $accountId, ChangeFtpAccountPasswordRequest $request): void
    {
        $this->http->request(
            'PATCH',
            "/api/v1/domains/{$domainId}/ftp-accounts/{$accountId}/password",
            body: $request->toArray(),
        );
    }

    public function delete(int $domainId, int $accountId): void
    {
        $this->http->request('DELETE', "/api/v1/domains/{$domainId}/ftp-accounts/{$accountId}");
    }
}
