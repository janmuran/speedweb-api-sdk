<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

use JanMuran\SpeedwebApiSdk\Model\Concern\HasExtraProperties;

/**
 * Customer invoice from the main administration DB (same data as the
 * `/invoices` UI), distinct from the reseller billing system's {@see Invoice}.
 * The admin DB may attach further columns beyond the ones enumerated here
 * (`additionalProperties: true` in the spec) — those are preserved in
 * {@see self::$extra} and round-tripped by toArray().
 */
final class CustomerInvoice implements ModelInterface
{
    use HasExtraProperties;

    private const KNOWN_KEYS = [
        'id', 'number', 'vs', 'invoice_type', 'date_issue', 'due_date', 'date_paid', 'date_tax',
        'amount', 'vat', 'total', 'due_amount', 'currency', 'customer_id', 'company', 'street',
        'city', 'zip', 'ico', 'dic', 'icdph', 'email', 'memo', 'invoice_items',
    ];

    /**
     * @param InvoiceItem[] $invoiceItems
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly int $id,
        public readonly int $number,
        public readonly string $vs,
        public readonly string $invoiceType,
        public readonly string $dateIssue,
        public readonly string $dueDate,
        public readonly ?string $datePaid,
        public readonly ?string $dateTax,
        public readonly float $amount,
        public readonly float $vat,
        public readonly float $total,
        public readonly float $dueAmount,
        public readonly string $currency,
        public readonly int $customerId,
        public readonly ?string $company,
        public readonly ?string $street,
        public readonly ?string $city,
        public readonly ?string $zip,
        public readonly ?string $ico,
        public readonly ?string $dic,
        public readonly ?string $icdph,
        public readonly ?string $email,
        public readonly ?string $memo,
        public readonly array $invoiceItems = [],
        public readonly array $extra = [],
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: (int) $data['id'],
            number: (int) $data['number'],
            vs: (string) $data['vs'],
            invoiceType: (string) $data['invoice_type'],
            dateIssue: (string) $data['date_issue'],
            dueDate: (string) $data['due_date'],
            datePaid: isset($data['date_paid']) ? (string) $data['date_paid'] : null,
            dateTax: isset($data['date_tax']) ? (string) $data['date_tax'] : null,
            amount: (float) $data['amount'],
            vat: (float) $data['vat'],
            total: (float) $data['total'],
            dueAmount: (float) $data['due_amount'],
            currency: (string) $data['currency'],
            customerId: (int) $data['customer_id'],
            company: isset($data['company']) ? (string) $data['company'] : null,
            street: isset($data['street']) ? (string) $data['street'] : null,
            city: isset($data['city']) ? (string) $data['city'] : null,
            zip: isset($data['zip']) ? (string) $data['zip'] : null,
            ico: isset($data['ico']) ? (string) $data['ico'] : null,
            dic: isset($data['dic']) ? (string) $data['dic'] : null,
            icdph: isset($data['icdph']) ? (string) $data['icdph'] : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
            memo: isset($data['memo']) ? (string) $data['memo'] : null,
            invoiceItems: array_map(
                static fn (array $item): InvoiceItem => InvoiceItem::fromArray($item),
                $data['invoice_items'] ?? [],
            ),
            extra: self::pluckExtra($data, self::KNOWN_KEYS),
        );
    }

    public function toArray(): array
    {
        return $this->mergeExtra([
            'id' => $this->id,
            'number' => $this->number,
            'vs' => $this->vs,
            'invoice_type' => $this->invoiceType,
            'date_issue' => $this->dateIssue,
            'due_date' => $this->dueDate,
            'date_paid' => $this->datePaid,
            'date_tax' => $this->dateTax,
            'amount' => $this->amount,
            'vat' => $this->vat,
            'total' => $this->total,
            'due_amount' => $this->dueAmount,
            'currency' => $this->currency,
            'customer_id' => $this->customerId,
            'company' => $this->company,
            'street' => $this->street,
            'city' => $this->city,
            'zip' => $this->zip,
            'ico' => $this->ico,
            'dic' => $this->dic,
            'icdph' => $this->icdph,
            'email' => $this->email,
            'memo' => $this->memo,
            'invoice_items' => array_map(static fn (InvoiceItem $item): array => $item->toArray(), $this->invoiceItems),
        ]);
    }
}
