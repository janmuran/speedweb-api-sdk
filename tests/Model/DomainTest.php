<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Model;

use JanMuran\SpeedwebApiSdk\Model\Domain;
use PHPUnit\Framework\TestCase;

final class DomainTest extends TestCase
{
    public function testFromArrayToArrayRoundTrip(): void
    {
        $data = [
            'id' => 1,
            'domain' => 'example.sk',
            'server' => 'web1',
            'mailserver' => 'mail1',
            'email' => 10,
            'ftp' => 122,
            'db' => 10,
            'quota_updated' => '2026-07-08',
        ];

        $domain = Domain::fromArray($data);

        self::assertSame($data, $domain->toArray());
    }
}
