<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Model;

use JanMuran\SpeedwebApiSdk\Model\BillingService;
use PHPUnit\Framework\TestCase;

final class BillingServiceTest extends TestCase
{
    public function testFromArrayToArrayRoundTripPreservesUnknownColumns(): void
    {
        $data = [
            'id' => 600,
            'customer' => 100,
            'text' => 'Webhosting example.sk',
            'price' => 49.0,
            'date' => '2026-12-01',
            'platba' => 12,
            'type' => 'proforma',
            'service_id' => null,
            'domain_id' => null,
            'splatnost' => 14,
            'discount' => 0.0,
            'quantity' => 1.0,
            'stoped' => 0,
            'legacy_column' => 'kept-as-is',
        ];

        $service = BillingService::fromArray($data);

        self::assertSame('kept-as-is', $service->extra['legacy_column']);
        self::assertSame($data, $service->toArray());
    }
}
