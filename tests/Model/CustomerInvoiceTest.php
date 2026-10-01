<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Model;

use JanMuran\SpeedwebApiSdk\Model\CustomerInvoice;
use PHPUnit\Framework\TestCase;

final class CustomerInvoiceTest extends TestCase
{
    public function testFromArrayToArrayRoundTripPreservesUnknownColumns(): void
    {
        $data = [
            'id' => 12345,
            'number' => 2024001,
            'vs' => '2024001',
            'invoice_type' => 'ostra',
            'date_issue' => '2026-01-01',
            'due_date' => '2026-01-15',
            'date_paid' => null,
            'date_tax' => null,
            'amount' => 49.0,
            'vat' => 9.8,
            'total' => 58.8,
            'due_amount' => 0.0,
            'currency' => 'eur',
            'customer_id' => 100,
            'company' => 'Example s.r.o.',
            'street' => null,
            'city' => null,
            'zip' => null,
            'ico' => null,
            'dic' => null,
            'icdph' => null,
            'email' => null,
            'memo' => null,
            'invoice_items' => [],
            // Column not enumerated in the OpenAPI schema (additionalProperties: true).
            'internal_note' => 'flagged for review',
        ];

        $invoice = CustomerInvoice::fromArray($data);

        self::assertSame('flagged for review', $invoice->extra['internal_note']);
        self::assertSame($data, $invoice->toArray());
    }
}
