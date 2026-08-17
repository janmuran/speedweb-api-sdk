<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Laravel-style pagination envelope (`current_page`, `data`, `per_page`,
 * `total`, `last_page`) used by the paginated list endpoints.
 *
 * @template T of ModelInterface
 * @implements IteratorAggregate<int, T>
 */
final class PaginatedCollection implements IteratorAggregate, Countable
{
    /**
     * @param T[] $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $lastPage,
    ) {
    }

    /**
     * @template TItem of ModelInterface
     * @param array<string, mixed> $data
     * @param class-string<TItem> $itemClass
     * @return self<TItem>
     */
    public static function fromArray(array $data, string $itemClass): self
    {
        $items = array_map(
            static fn (array $item) => $itemClass::fromArray($item),
            $data['data'] ?? [],
        );

        return new self(
            items: $items,
            currentPage: (int) ($data['current_page'] ?? 1),
            perPage: (int) ($data['per_page'] ?? count($items)),
            total: (int) ($data['total'] ?? count($items)),
            lastPage: (int) ($data['last_page'] ?? 1),
        );
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
