<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

use JanMuran\SpeedwebApiSdk\Model\Concern\HasExtraProperties;

/**
 * Invoice from the billing system. The billing system may attach further
 * columns beyond the ones enumerated here (`additionalProperties: true` in
 * the spec) — those are preserved in {@see self::$extra} and round-tripped
 * by toArray().
 */
final class Invoice implements ModelInterface
{
    use HasExtraProperties;

    private const KNOWN_KEYS = [
        'id', 'number', 'vs', 'invoice_type', 'date_issue', 'due_date', 'amount',
        'vat', 'total', 'due_amount', 'currency', 'customer_id', 'company', 'invoice_items',
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
        public readonly float $amount,
        public readonly float $vat,
        public readonly float $total,
        public readonly float $dueAmount,
        public readonly string $currency,
        public readonly int $customerId,
        public readonly ?string $company,
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
            amount: (float) $data['amount'],
            vat: (float) $data['vat'],
            total: (float) $data['total'],
            dueAmount: (float) $data['due_amount'],
            currency: (string) $data['currency'],
            customerId: (int) $data['customer_id'],
            company: isset($data['company']) ? (string) $data['company'] : null,
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
            'amount' => $this->amount,
            'vat' => $this->vat,
            'total' => $this->total,
            'due_amount' => $this->dueAmount,
            'currency' => $this->currency,
            'customer_id' => $this->customerId,
            'company' => $this->company,
            'invoice_items' => array_map(static fn (InvoiceItem $item): array => $item->toArray(), $this->invoiceItems),
        ]);
    }
}
