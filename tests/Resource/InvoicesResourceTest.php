<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Model\CustomerInvoice;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class InvoicesResourceTest extends TestCase
{
    private static function customerInvoiceData(): array
    {
        return [
            'id' => 12345, 'number' => 2024001, 'vs' => '2024001', 'invoice_type' => 'ostra',
            'date_issue' => '2026-01-01', 'due_date' => '2026-01-15', 'date_paid' => null, 'date_tax' => null,
            'amount' => 49.0, 'vat' => 9.8, 'total' => 58.8, 'due_amount' => 0.0, 'currency' => 'eur',
            'customer_id' => 100, 'company' => 'Example s.r.o.', 'street' => null, 'city' => null,
            'zip' => null, 'ico' => null, 'dic' => null, 'icdph' => null, 'email' => null, 'memo' => null,
            'invoice_items' => [],
        ];
    }

    public function testListReturnsPaginatedCollection(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'current_page' => 1,
            'data' => [self::customerInvoiceData()],
            'per_page' => 10,
            'total' => 1,
            'last_page' => 1,
        ]));

        $invoices = $client->invoices()->list(page: 1, search: 'example.sk');

        self::assertCount(1, $invoices);
        self::assertInstanceOf(CustomerInvoice::class, $invoices->items[0]);
        self::assertSame('page=1&search=example.sk', $mock->getLastRequest()->getUri()->getQuery());
    }

    public function testGetReturnsInvoice(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, ['data' => self::customerInvoiceData()]));

        $invoice = $client->invoices()->get(12345);

        self::assertInstanceOf(CustomerInvoice::class, $invoice);
        self::assertSame(12345, $invoice->id);
    }

    public function testGetNotFoundThrowsApiException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(404, ['message' => 'Invoice not found']));

        $this->expectException(ApiException::class);
        $client->invoices()->get(999);
    }
}
