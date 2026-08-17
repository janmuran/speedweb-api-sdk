<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\Subuser;

/**
 * `Subusers` tag: main account subusers.
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
}
