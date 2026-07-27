<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Countable;
use IteratorAggregate;

/**
 * @template T
 * @extends IteratorAggregate<int, T>
 */
interface ResultSetInterface extends IteratorAggregate, Countable
{
    /**
     * @return list<T>
     */
    public function all(): array;

    public function isEmpty(): bool;

    /**
     * @return T|null
     */
    public function first(): mixed;
}
