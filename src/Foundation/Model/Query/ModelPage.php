<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Query;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @template T of object
 * @implements IteratorAggregate<int, T>
 */
final readonly class ModelPage implements Countable, IteratorAggregate
{
    /** @var list<T> */
    private array $items;

    /** @param list<T> $items */
    public function __construct(array $items, private int $page, private int $perPage)
    {
        if ($page < 1 || $perPage < 1) {
            throw new \InvalidArgumentException('Model page and per-page values must be positive.');
        }

        $this->items = array_values($items);
    }

    /** @return list<T> */
    public function items(): array
    {
        return $this->items;
    }

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function hasNextPage(): bool
    {
        return $this->count() === $this->perPage;
    }

    /** @return Traversable<int, T> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
