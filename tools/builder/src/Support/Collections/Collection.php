<?php
declare(strict_types=1);

namespace Sif\Support\Collections;

use Sif\Support\Contracts\CollectionInterface;

/** @template T @implements CollectionInterface<T> */
abstract class Collection implements CollectionInterface
{
    public function isEmpty(): bool { return $this->count() === 0; }
}
