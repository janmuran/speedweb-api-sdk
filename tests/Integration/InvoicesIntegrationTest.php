<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Tests\Integration;

final class InvoicesIntegrationTest extends IntegrationTestCase
{
    public function testListInvoices(): void
    {
        $invoices = $this->client->invoices()->list(page: 1);

        self::assertGreaterThanOrEqual(0, $invoices->total);
        self::assertCount(count($invoices->items), $invoices);

        fwrite(STDERR, sprintf("\n[invoices] %d invoice(s) total\n", $invoices->total));
    }

    public function testGetInvoice(): void
    {
        $invoices = $this->client->invoices()->list(page: 1);
        if ($invoices->items === []) {
            self::markTestSkipped('No invoices on this account to fetch by id.');
        }

        $expected = $invoices->items[0];
        $invoice = $this->client->invoices()->get($expected->id);

        self::assertSame($expected->id, $invoice->id);
        self::assertSame($expected->number, $invoice->number);
    }
}
