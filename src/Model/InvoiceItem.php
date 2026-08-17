<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

use JanMuran\SpeedwebApiSdk\Model\Concern\HasExtraProperties;

/**
 * Invoice line item. The billing system may attach further columns beyond
 * the ones enumerated here (`additionalProperties: true` in the spec) —
 * those are preserved in {@see self::$extra} and round-tripped by toArray().
 */
final class InvoiceItem implements ModelInterface
{
    use HasExtraProperties;

    private const KNOWN_KEYS = [
        'id', 'invoice_id', 'service_id', 'text', 'unit_price', 'quantity',
        'amount', 'vat', 'total', 'service_start_date', 'service_end_date', 'domain_id',
    ];

    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly int $id,
        public readonly int $invoiceId,
        public readonly ?int $serviceId,
        public readonly string $text,
        public readonly float $unitPrice,
        public readonly float $quantity,
        public readonly float $amount,
        public readonly float $vat,
        public readonly float $total,
        public readonly ?string $serviceStartDate,
        public readonly ?string $serviceEndDate,
        public readonly ?int $domainId,
        public readonly array $extra = [],
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: (int) $data['id'],
            invoiceId: (int) $data['invoice_id'],
            serviceId: isset($data['service_id']) ? (int) $data['service_id'] : null,
            text: (string) $data['text'],
            unitPrice: (float) $data['unit_price'],
            quantity: (float) $data['quantity'],
            amount: (float) $data['amount'],
            vat: (float) $data['vat'],
            total: (float) $data['total'],
            serviceStartDate: isset($data['service_start_date']) ? (string) $data['service_start_date'] : null,
            serviceEndDate: isset($data['service_end_date']) ? (string) $data['service_end_date'] : null,
            domainId: isset($data['domain_id']) ? (int) $data['domain_id'] : null,
            extra: self::pluckExtra($data, self::KNOWN_KEYS),
        );
    }

    public function toArray(): array
    {
        return $this->mergeExtra([
            'id' => $this->id,
            'invoice_id' => $this->invoiceId,
            'service_id' => $this->serviceId,
            'text' => $this->text,
            'unit_price' => $this->unitPrice,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'vat' => $this->vat,
            'total' => $this->total,
            'service_start_date' => $this->serviceStartDate,
            'service_end_date' => $this->serviceEndDate,
            'domain_id' => $this->domainId,
        ]);
    }
}
