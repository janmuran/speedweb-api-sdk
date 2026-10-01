<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Model\CustomerInvoice;
use JanMuran\SpeedwebApiSdk\Model\PaginatedCollection;

/**
 * `Invoices` tag: customer invoices from the main administration DB
 * (`/invoices`), distinct from the reseller billing system's
 * {@see BillingResource} invoices.
 */
final class InvoicesResource extends AbstractResource
{
    /**
     * @return PaginatedCollection<CustomerInvoice>
     */
    public function list(
        ?int $page = null,
        ?string $search = null,
        ?int $number = null,
        ?string $vs = null,
        ?int $status = null,
        ?int $type = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): PaginatedCollection {
        $data = $this->http->request('GET', '/api/v1/invoices', [
            'page' => $page,
            'search' => $search,
            'number' => $number,
            'vs' => $vs,
            'status' => $status,
            'type' => $type,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);

        return PaginatedCollection::fromArray($data, CustomerInvoice::class);
    }

    public function get(int $id): CustomerInvoice
    {
        $data = $this->http->request('GET', "/api/v1/invoices/{$id}");

        return CustomerInvoice::fromArray($data['data'] ?? []);
    }
}
