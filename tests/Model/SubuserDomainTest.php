<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Model;

use JanMuran\SpeedwebApiSdk\Model\SubuserDomain;
use PHPUnit\Framework\TestCase;

final class SubuserDomainTest extends TestCase
{
    public function testFromArrayToArrayRoundTrip(): void
    {
        $data = [
            'id' => 10,
            'domain_id' => 1,
            'domain' => 'example.sk',
            'web' => 1,
            'ftp' => 1,
            'email' => 1,
            'db' => 1,
            'dns' => 1,
            'edit' => 1,
            'perm_modules' => 'ALL',
        ];

        $subuserDomain = SubuserDomain::fromArray($data);

        self::assertSame($data, $subuserDomain->toArray());
    }
}
