<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Resource;

use JanMuran\SpeedwebApiSdk\Exception\ApiException;
use JanMuran\SpeedwebApiSdk\Model\BillingService;
use JanMuran\SpeedwebApiSdk\Model\Invoice;
use JanMuran\SpeedwebApiSdk\Model\Request\CreateBillingServiceRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\UpdateBillingServiceRequest;
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

    private static function billingServiceData(): array
    {
        return [
            'id' => 600, 'customer' => 100, 'text' => 'Webhosting example.sk', 'price' => 49.0,
            'date' => '2026-12-01', 'platba' => 12, 'type' => 'proforma', 'service_id' => 1,
            'domain_id' => null, 'splatnost' => 14, 'discount' => 0.0, 'quantity' => 1.0, 'stoped' => 0,
        ];
    }

    public function testGetServiceReturnsService(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, ['data' => self::billingServiceData()]));

        $service = $client->billing()->getService(600);

        self::assertInstanceOf(BillingService::class, $service);
        self::assertSame(600, $service->id);
    }

    public function testCreateServiceReturnsCreatedService(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(201, ['data' => self::billingServiceData()]));

        $service = $client->billing()->createService(new CreateBillingServiceRequest(
            customer: 100,
            serviceId: 1,
            platba: 12,
            price: 49.0,
            text: 'Webhosting example.sk',
            date: '2026-12-01',
            splatnost: 14,
            type: 'proforma',
        ));

        self::assertInstanceOf(BillingService::class, $service);
        self::assertSame('POST', $mock->getLastRequest()->getMethod());
    }

    public function testUpdateServiceReturnsUpdatedService(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(200, ['data' => self::billingServiceData()]));

        $service = $client->billing()->updateService(600, new UpdateBillingServiceRequest(
            customer: 100,
            serviceId: 1,
            platba: 12,
            price: 49.0,
            text: 'Webhosting example.sk',
            date: '2026-12-01',
            splatnost: 14,
            type: 'proforma',
        ));

        self::assertInstanceOf(BillingService::class, $service);
        self::assertSame('PATCH', $mock->getLastRequest()->getMethod());
    }

    public function testDeleteServiceSendsDeleteRequest(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(204, []));

        $client->billing()->deleteService(600);

        self::assertSame('DELETE', $mock->getLastRequest()->getMethod());
        self::assertStringEndsWith('/api/v1/billing/services/600', (string) $mock->getLastRequest()->getUri());
    }

    public function testDeleteServiceNotFoundThrowsApiException(): void
    {
        [$client, $mock] = MockApiClientFactory::create();
        $mock->addResponse(MockApiClientFactory::jsonResponse(404, ['message' => 'Service not found']));

        $this->expectException(ApiException::class);
        $client->billing()->deleteService(999);
    }
}
