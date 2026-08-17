<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\Domain;

/**
 * `Domains` tag: customer domain list.
 */
final class DomainsResource extends AbstractResource
{
    /**
     * @return Domain[]
     */
    public function list(): array
    {
        $data = $this->http->request('GET', '/api/v1/domains');

        return array_map(
            static fn (array $item): Domain => Domain::fromArray($item),
            $data['data'] ?? [],
        );
    }
}
