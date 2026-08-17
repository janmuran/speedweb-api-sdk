<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\BillingService;
use JanMuran\SpeedwebApiSdk\Model\Invoice;
use JanMuran\SpeedwebApiSdk\Model\PaginatedCollection;

/**
 * `Billing` tag: customer invoices and billed services.
 */
final class BillingResource extends AbstractResource
{
    /**
     * @return PaginatedCollection<Invoice>
     */
    public function listInvoices(
        ?int $page = null,
        ?string $text = null,
        ?int $number = null,
        ?string $vs = null,
        ?string $invoiceType = null,
        ?int $paid = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $status = null,
    ): PaginatedCollection {
        $data = $this->http->request('GET', '/api/v1/billing/invoices', [
            'page' => $page,
            'text' => $text,
            'number' => $number,
            'vs' => $vs,
            'invoice_type' => $invoiceType,
            'paid' => $paid,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'status' => $status,
        ]);

        return PaginatedCollection::fromArray($data, Invoice::class);
    }

    public function getInvoice(int $id): Invoice
    {
        $data = $this->http->request('GET', "/api/v1/billing/invoices/{$id}");

        return Invoice::fromArray($data['data'] ?? []);
    }

    /**
     * @return PaginatedCollection<BillingService>
     */
    public function listServices(?int $page = null, ?string $search = null): PaginatedCollection
    {
        $data = $this->http->request('GET', '/api/v1/billing/services', [
            'page' => $page,
            'search' => $search,
        ]);

        return PaginatedCollection::fromArray($data, BillingService::class);
    }
}
