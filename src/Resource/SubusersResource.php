<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\Request\AssignSubuserDomainRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\RemoveSubuserDomainRequest;
use JanMuran\SpeedwebApiSdk\Model\Subuser;
use JanMuran\SpeedwebApiSdk\Model\SubuserDomain;

/**
 * `Subusers` tag: main account subusers and their assigned domains.
 */
final class SubusersResource extends AbstractResource
{
    /**
     * @return Subuser[]
     */
    public function list(): array
    {
        $data = $this->http->request('GET', '/api/v1/subusers');

        return array_map(
            static fn (array $item): Subuser => Subuser::fromArray($item),
            $data['data'] ?? [],
        );
    }

    /**
     * @return SubuserDomain[]
     */
    public function listDomains(int $subuserId): array
    {
        $data = $this->http->request('GET', "/api/v1/subusers/{$subuserId}/domains");

        return array_map(
            static fn (array $item): SubuserDomain => SubuserDomain::fromArray($item),
            $data['data'] ?? [],
        );
    }

    public function assignDomain(int $subuserId, AssignSubuserDomainRequest $request): SubuserDomain
    {
        $data = $this->http->request(
            'POST',
            "/api/v1/subusers/{$subuserId}/domains",
            body: $request->toArray(),
        );

        return SubuserDomain::fromArray($data['data'] ?? []);
    }

    public function unassignDomain(int $subuserId, RemoveSubuserDomainRequest $request): void
    {
        $this->http->request(
            'DELETE',
            "/api/v1/subusers/{$subuserId}/domains",
            body: $request->toArray(),
        );
    }
}
