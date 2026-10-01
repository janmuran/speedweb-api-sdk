<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\DnsRecord;
use JanMuran\SpeedwebApiSdk\Model\PaginatedCollection;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateDnsRecordRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\UpdateDnsRecordRequest;

/**
 * `DNS` tag: domain DNS record management.
 */
final class DnsResource extends AbstractResource
{
    /**
     * @return PaginatedCollection<DnsRecord>
     */
    public function list(int $domainId, ?int $page = null, ?int $perPage = null): PaginatedCollection
    {
        $data = $this->http->request('GET', "/api/v1/domains/{$domainId}/dns-records", [
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return PaginatedCollection::fromArray($data, DnsRecord::class);
    }

    public function create(int $domainId, CreateDnsRecordRequest $request, ?string $idempotencyKey = null): DnsRecord
    {
        $data = $this->http->request(
            'POST',
            "/api/v1/domains/{$domainId}/dns-records",
            body: $request->toArray(),
            headers: $idempotencyKey !== null ? ['Idempotency-Key' => $idempotencyKey] : [],
        );

        return DnsRecord::fromArray($data['data'] ?? []);
    }

    public function update(int $domainId, int $recordId, UpdateDnsRecordRequest $request): DnsRecord
    {
        $data = $this->http->request(
            'PATCH',
            "/api/v1/domains/{$domainId}/dns-records/{$recordId}",
            body: $request->toArray(),
        );

        return DnsRecord::fromArray($data['data'] ?? []);
    }

    public function delete(int $domainId, int $recordId): void
    {
        $this->http->request('DELETE', "/api/v1/domains/{$domainId}/dns-records/{$recordId}");
    }
}
