<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model\Request;

use InvalidArgumentException;
use JanMuran\SpeedwebApiSdk\Model\ModelInterface;

final class CreateBillingServiceRequest implements ModelInterface
{
    private const ALLOWED_PLATBA = [1, 2, 3, 4, 6, 12];
    private const ALLOWED_TYPES = ['proforma', 'ostra'];

    public function __construct(
        public readonly int $customer,
        public readonly int $serviceId,
        public readonly int $platba,
        public readonly float $price,
        public readonly string $text,
        public readonly string $date,
        public readonly int $splatnost,
        public readonly string $type,
        public readonly float $discount = 0.0,
        public readonly float $quantity = 1.0,
        public readonly ?int $domainId = null,
    ) {
        if (!in_array($platba, self::ALLOWED_PLATBA, true)) {
            throw new InvalidArgumentException('platba must be one of: ' . implode(', ', self::ALLOWED_PLATBA) . '.');
        }

        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            throw new InvalidArgumentException('type must be one of: ' . implode(', ', self::ALLOWED_TYPES) . '.');
        }
    }

    public static function fromArray(array $data): static
    {
        return new self(
            customer: (int) $data['customer'],
            serviceId: (int) $data['service_id'],
            platba: (int) $data['platba'],
            price: (float) $data['price'],
            text: (string) $data['text'],
            date: (string) $data['date'],
            splatnost: (int) $data['splatnost'],
            type: (string) $data['type'],
            discount: isset($data['discount']) ? (float) $data['discount'] : 0.0,
            quantity: isset($data['quantity']) ? (float) $data['quantity'] : 1.0,
            domainId: isset($data['domain_id']) ? (int) $data['domain_id'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'customer' => $this->customer,
            'service_id' => $this->serviceId,
            'platba' => $this->platba,
            'price' => (string) $this->price,
            'text' => $this->text,
            'date' => $this->date,
            'discount' => (string) $this->discount,
            'quantity' => (string) $this->quantity,
            'splatnost' => (string) $this->splatnost,
            'type' => $this->type,
            'domain_id' => $this->domainId,
        ];
    }
}
