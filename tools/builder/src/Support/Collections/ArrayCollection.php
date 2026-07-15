<?php
declare(strict_types=1);

namespace Sif\Support\Collections;

use ArrayIterator;
use IteratorAggregate;
use Sif\Support\Contracts\CollectionInterface;
use Sif\Support\Exceptions\InvalidArgumentException;
use Traversable;

/** @template T @extends Collection<T> */
final class ArrayCollection extends Collection
{
    /** @var array<int, T> */
    private array $items;
    /** @param iterable<T> $items */
    public function __construct(iterable $items = []) { $this->items = is_array($items) ? array_values($items) : iterator_to_array($items, false); }
    public function count(): int { return count($this->items); }
    public function getIterator(): Traversable { return new ArrayIterator($this->items); }
    public function first(): mixed { if ($this->items === []) { throw new InvalidArgumentException('Collection is empty.'); } return $this->items[0]; }
    public function last(): mixed { if ($this->items === []) { throw new InvalidArgumentException('Collection is empty.'); } return $this->items[array_key_last($this->items)]; }
    public function toArray(): array { return $this->items; }
    public function contains(mixed $value): bool { return in_array($value, $this->items, true); }
    public function filter(callable $predicate): static { return new self(array_values(array_filter($this->items, $predicate))); }
    public function map(callable $mapper): CollectionInterface { return new self(array_map($mapper, $this->items)); }
    /** @param T $value */
    public function with(mixed $value): self { return new self([...$this->items, $value]); }
}
