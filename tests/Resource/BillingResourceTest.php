<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Model\Invoice;
use JanMuran\SpeedwebApiSdk\Tests\Support\MockApiClientFactory;
use PHPUnit\Framework\TestCase;

final class BillingResourceTest extends TestCase
{
    public function testListInvoicesReturnsPaginatedCollection(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, [
            'current_page' => 1,
            'data' => [
                [
                    'id' => 1, 'number' => 2024001, 'vs' => '2024001', 'invoice_type' => 'ostra',
                    'date_issue' => '2026-01-01', 'due_date' => '2026-01-15', 'amount' => 49.0,
                    'vat' => 9.8, 'total' => 58.8, 'due_amount' => 0.0, 'currency' => 'EUR',
                    'customer_id' => 100, 'company' => null, 'invoice_items' => [],
                ],
            ],
            'per_page' => 10,
            'total' => 1,
            'last_page' => 1,
        ]));

        $invoices = $client->billing()->listInvoices(page: 1, paid: 1);

        self::assertCount(1, $invoices);
        self::assertInstanceOf(Invoice::class, $invoices->items[0]);
        self::assertSame('page=1&paid=1', $mock->getLastRequest()->getUri()->getQuery());
    }

    public function testGetInvoiceNotFoundThrowsApiException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(404, ['message' => 'Invoice not found']));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invoice not found');

        $client->billing()->getInvoice(999);
    }
}
