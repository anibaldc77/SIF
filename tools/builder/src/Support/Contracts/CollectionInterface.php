<?php
declare(strict_types=1);

namespace Sif\Support\Contracts;

use Countable;
use IteratorAggregate;

/** @template T @extends IteratorAggregate<int, T> */
interface CollectionInterface extends Countable, IteratorAggregate
{
    public function isEmpty(): bool;
    /** @return T */
    public function first();
    /** @return T */
    public function last();
    /** @return array<int, T> */
    public function toArray(): array;
    /** @param T $value */
    public function contains(mixed $value): bool;
    /** @param callable(T): bool $predicate @return static */
    public function filter(callable $predicate): static;
    /** @template R @param callable(T): R $mapper @return CollectionInterface<R> */
    public function map(callable $mapper): CollectionInterface;
}
