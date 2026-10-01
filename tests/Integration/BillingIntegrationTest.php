<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Integration;

use JanMuran\SpeedwebApiSdk\Model\Request\CreateBillingServiceRequest;
use JanMuran\SpeedwebApiSdk\Model\Request\UpdateBillingServiceRequest;

final class BillingIntegrationTest extends IntegrationTestCase
{
    public function testListInvoices(): void
    {
        $invoices = $this->client->billing()->listInvoices(page: 1);

        self::assertGreaterThanOrEqual(0, $invoices->total);
        self::assertCount(count($invoices->items), $invoices);

        fwrite(STDERR, sprintf("\n[billing] %d invoice(s) total\n", $invoices->total));
    }

    public function testGetInvoice(): void
    {
        $invoices = $this->client->billing()->listInvoices(page: 1);
        if ($invoices->items === []) {
            self::markTestSkipped('No invoices on this account to fetch by id.');
        }

        $expected = $invoices->items[0];
        $invoice = $this->client->billing()->getInvoice($expected->id);

        self::assertSame($expected->id, $invoice->id);
        self::assertSame($expected->number, $invoice->number);
    }

    public function testListServices(): void
    {
        $services = $this->client->billing()->listServices(page: 1);

        self::assertGreaterThanOrEqual(0, $services->total);
        self::assertCount(count($services->items), $services);

        fwrite(STDERR, sprintf("\n[billing] %d billed service(s) total\n", $services->total));
    }

    public function testGetService(): void
    {
        $services = $this->client->billing()->listServices(page: 1);
        if ($services->items === []) {
            self::markTestSkipped('No billed services on this account to fetch by id.');
        }

        $expected = $services->items[0];
        $service = $this->client->billing()->getService($expected->id);

        self::assertSame($expected->id, $service->id);
        self::assertSame($expected->customer, $service->customer);
    }

    /**
     * Creates a throwaway billed service, updates its price, then deletes
     * it — nothing is left behind. The customer id defaults to the one
     * copied from an existing service, but that's not guaranteed to be
     * writable (the API validates it against access the write endpoint
     * checks separately from read) — override with
     * SPEEDWEB_TEST_BILLING_CUSTOMER_ID to pin a known-valid one.
     *
     * Opt-in via SPEEDWEB_TEST_BILLING_WRITE=1 (separate from
     * SPEEDWEB_TEST_CREATE) because billed services can trigger real
     * recurring invoices if left in place.
     */
    public function testCreateUpdateAndDeleteService(): void
    {
        $this->requireBillingWriteOptIn();

        $existing = $this->client->billing()->listServices(page: 1);
        if ($existing->items === []) {
            self::markTestSkipped('No existing billed services to copy customer/service_id from.');
        }
        $template = $existing->items[0];
        $customerId = $this->billingCustomerId($template->customer);

        $created = $this->client->billing()->createService(new CreateBillingServiceRequest(
            customer: $customerId,
            serviceId: $template->serviceId ?? 1,
            platba: 12,
            price: 1.0,
            text: 'speedweb-sdk integration test service ' . self::uniqueSuffix(),
            date: date('Y-m-d', strtotime('+1 year')),
            splatnost: 14,
            type: 'proforma',
        ));

        self::assertNotSame(0, $created->id);
        fwrite(STDERR, sprintf("\n[billing] created service #%d\n", $created->id));

        $updated = $this->client->billing()->updateService($created->id, new UpdateBillingServiceRequest(
            customer: $created->customer,
            serviceId: $created->serviceId ?? $template->serviceId ?? 1,
            platba: $created->platba,
            price: 2.0,
            text: $created->text,
            date: $created->date,
            splatnost: $created->splatnost,
            type: $created->type,
        ));
        self::assertSame(2.0, $updated->price);
        fwrite(STDERR, sprintf("[billing] updated price of service #%d to 2.0\n", $created->id));

        $this->client->billing()->deleteService($created->id);
        fwrite(STDERR, sprintf("[billing] deleted service #%d — no leftovers\n", $created->id));
    }
}
