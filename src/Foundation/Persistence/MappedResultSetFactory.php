<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use Sif\Foundation\Contracts\MapperInterface;

/**
 * @template T of object
 */
final readonly class MappedResultSetFactory
{
    /**
     * @param MapperInterface<T> $mapper
     */
    public function __construct(
        private MapperInterface $mapper,
    ) {
    }

    /**
     * @param list<StorageRecord> $records
     *
     * @return ResultSet<T>
     */
    public function create(array $records): ResultSet
    {
        $items = [];

        foreach ($records as $record) {
            $items[] = $this->mapper->hydrate($record);
        }

        return new ResultSet($items);
    }
}
