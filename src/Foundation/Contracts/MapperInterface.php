<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Persistence\StorageRecord;

/**
 * @template T of object
 */
interface MapperInterface
{
    /**
     * @return T
     */
    public function hydrate(StorageRecord $record): object;

    /**
     * @param T $object
     */
    public function extract(object $object): StorageRecord;
}
