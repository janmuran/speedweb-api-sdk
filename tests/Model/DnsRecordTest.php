<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Model;

use JanMuran\SpeedwebApiSdk\Model\DnsRecord;
use PHPUnit\Framework\TestCase;

final class DnsRecordTest extends TestCase
{
    public function testFromArrayToArrayRoundTrip(): void
    {
        $data = [
            'id' => 100,
            'domain_id' => 1,
            'name' => 'www',
            'ttl' => 3600,
            'type' => 'A',
            'value' => '1.2.3.4',
            'memo' => 'primary A record',
        ];

        $record = DnsRecord::fromArray($data);

        self::assertSame($data, $record->toArray());
    }

    public function testNullableMemoDefaultsToNull(): void
    {
        $record = DnsRecord::fromArray([
            'id' => 1,
            'domain_id' => 1,
            'name' => '@',
            'ttl' => 3600,
            'type' => 'A',
            'value' => '1.2.3.4',
        ]);

        self::assertNull($record->memo);
    }
}
