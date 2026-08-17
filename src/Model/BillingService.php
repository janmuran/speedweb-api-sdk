<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

use JanMuran\SpeedwebApiSdk\Model\Concern\HasExtraProperties;

/**
 * Recurring billed service (topay). The billing system may attach further
 * columns beyond the ones enumerated here (`additionalProperties: true` in
 * the spec) — those are preserved in {@see self::$extra} and round-tripped
 * by toArray().
 */
final class BillingService implements ModelInterface
{
    use HasExtraProperties;

    private const KNOWN_KEYS = [
        'id', 'customer', 'text', 'price', 'date', 'platba', 'type', 'service_id',
        'domain_id', 'splatnost', 'discount', 'quantity', 'stoped',
    ];

    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly int $id,
        public readonly int $customer,
        public readonly string $text,
        public readonly float $price,
        public readonly string $date,
        public readonly int $platba,
        public readonly string $type,
        public readonly ?int $serviceId,
        public readonly ?int $domainId,
        public readonly int $splatnost,
        public readonly float $discount,
        public readonly float $quantity,
        public readonly int $stoped,
        public readonly array $extra = [],
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: (int) $data['id'],
            customer: (int) $data['customer'],
            text: (string) $data['text'],
            price: (float) $data['price'],
            date: (string) $data['date'],
            platba: (int) $data['platba'],
            type: (string) $data['type'],
            serviceId: isset($data['service_id']) ? (int) $data['service_id'] : null,
            domainId: isset($data['domain_id']) ? (int) $data['domain_id'] : null,
            splatnost: (int) $data['splatnost'],
            discount: (float) $data['discount'],
            quantity: (float) $data['quantity'],
            stoped: (int) $data['stoped'],
            extra: self::pluckExtra($data, self::KNOWN_KEYS),
        );
    }

    public function toArray(): array
    {
        return $this->mergeExtra([
            'id' => $this->id,
            'customer' => $this->customer,
            'text' => $this->text,
            'price' => $this->price,
            'date' => $this->date,
            'platba' => $this->platba,
            'type' => $this->type,
            'service_id' => $this->serviceId,
            'domain_id' => $this->domainId,
            'splatnost' => $this->splatnost,
            'discount' => $this->discount,
            'quantity' => $this->quantity,
            'stoped' => $this->stoped,
        ]);
    }
}
