<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use ArrayIterator;
use Sif\Foundation\Contracts\ResultSetInterface;
use Traversable;

/**
 * @template T
 * @implements ResultSetInterface<T>
 */
final readonly class ResultSet implements ResultSetInterface
{
    /**
     * @var list<T>
     */
    private array $items;

    /**
     * @param list<T> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = array_values($items);
    }

    /**
     * @return list<T>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    /**
     * @return T|null
     */
    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
