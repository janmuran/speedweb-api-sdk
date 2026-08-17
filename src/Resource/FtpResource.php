<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\FtpAccount;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateFtpAccountRequest;

/**
 * `FTP` tag: domain FTP accounts.
 */
final class FtpResource extends AbstractResource
{
    /**
     * @return FtpAccount[]
     */
    public function list(int $domainId): array
    {
        $data = $this->http->request('GET', "/api/v1/domains/{$domainId}/ftp-accounts");

        return array_map(
            static fn (array $item): FtpAccount => FtpAccount::fromArray($item),
            $data['data'] ?? [],
        );
    }

    public function create(int $domainId, CreateFtpAccountRequest $request): FtpAccount
    {
        $data = $this->http->request(
            'POST',
            "/api/v1/domains/{$domainId}/ftp-accounts",
            body: $request->toArray(),
        );

        return FtpAccount::fromArray($data['data'] ?? []);
    }
}
