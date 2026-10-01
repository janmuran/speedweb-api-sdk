<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Model;

use JanMuran\SpeedwebApiSdk\Model\DomainDnssec;
use PHPUnit\Framework\TestCase;

final class DomainDnssecTest extends TestCase
{
    public function testFromArrayToArrayRoundTrip(): void
    {
        $data = [
            'enabled' => true,
            'status' => 1,
            'last_signed' => '2026-01-01 12:00:00',
            'dsset' => 'base64payload==',
        ];

        $dnssec = DomainDnssec::fromArray($data);

        self::assertSame($data, $dnssec->toArray());
    }

    public function testDefaultsForNeverEnabledDomain(): void
    {
        $dnssec = DomainDnssec::fromArray(['enabled' => false]);

        self::assertFalse($dnssec->enabled);
        self::assertNull($dnssec->status);
        self::assertNull($dnssec->lastSigned);
        self::assertNull($dnssec->dsset);
    }
}
