<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\Domain;
use JanMuran\SpeedwebApiSdk\Model\DomainDnssec;
use JanMuran\SpeedwebApiSdk\Model\PaginatedCollection;
use JanMuran\SpeedwebApiSdk\Model\Request\SetDnssecRequest;

/**
 * `Domains` tag: customer domain list and DNSSEC settings.
 */
final class DomainsResource extends AbstractResource
{
    /**
     * @return PaginatedCollection<Domain>
     */
    public function list(?int $page = null, ?int $perPage = null): PaginatedCollection
    {
        $data = $this->http->request('GET', '/api/v1/domains', [
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return PaginatedCollection::fromArray($data, Domain::class);
    }

    public function getDnssec(int $domainId): DomainDnssec
    {
        $data = $this->http->request('GET', "/api/v1/domains/{$domainId}/dnssec");

        return DomainDnssec::fromArray($data['data'] ?? []);
    }

    public function setDnssec(int $domainId, SetDnssecRequest $request): DomainDnssec
    {
        $data = $this->http->request(
            'PUT',
            "/api/v1/domains/{$domainId}/dnssec",
            body: $request->toArray(),
        );

        return DomainDnssec::fromArray($data['data'] ?? []);
    }
}
