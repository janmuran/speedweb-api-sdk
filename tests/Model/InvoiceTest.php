<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Model;

use JanMuran\SpeedwebApiSdk\Model\Invoice;
use PHPUnit\Framework\TestCase;

final class InvoiceTest extends TestCase
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
            'amount' => 49.0,
            'vat' => 9.8,
            'total' => 58.8,
            'due_amount' => 0.0,
            'currency' => 'EUR',
            'customer_id' => 100,
            'company' => null,
            'invoice_items' => [
                [
                    'id' => 500,
                    'invoice_id' => 12345,
                    'service_id' => null,
                    'text' => 'Webhosting example.sk',
                    'unit_price' => 49.0,
                    'quantity' => 1.0,
                    'amount' => 49.0,
                    'vat' => 9.8,
                    'total' => 58.8,
                    'service_start_date' => null,
                    'service_end_date' => null,
                    'domain_id' => null,
                ],
            ],
            // Column not enumerated in the OpenAPI schema (additionalProperties: true).
            'internal_note' => 'flagged for review',
        ];

        $invoice = Invoice::fromArray($data);

        self::assertSame('flagged for review', $invoice->extra['internal_note']);
        self::assertCount(1, $invoice->invoiceItems);
        self::assertSame($data, $invoice->toArray());
    }
}
