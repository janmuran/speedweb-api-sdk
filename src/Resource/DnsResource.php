<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\DnsRecord;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateDnsRecordRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\UpdateDnsRecordRequest;

/**
 * `DNS` tag: domain DNS record management.
 */
final class DnsResource extends AbstractResource
{
    /**
     * @return DnsRecord[]
     */
    public function list(int $domainId): array
    {
        $data = $this->http->request('GET', "/api/v1/domains/{$domainId}/dns-records");

        return array_map(
            static fn (array $item): DnsRecord => DnsRecord::fromArray($item),
            $data['data'] ?? [],
        );
    }

    public function create(int $domainId, CreateDnsRecordRequest $request): DnsRecord
    {
        $data = $this->http->request(
            'POST',
            "/api/v1/domains/{$domainId}/dns-records",
            body: $request->toArray(),
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
}
