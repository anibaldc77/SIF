<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Persistence;

use Sif\Foundation\Contracts\QueryInterface;
use Sif\Foundation\Contracts\ReadRepositoryInterface;
use Sif\Foundation\Contracts\ResultSetInterface;
use Sif\Foundation\Contracts\WriteRepositoryInterface;
use Sif\Foundation\Persistence\RepositoryName;
use Sif\Foundation\Persistence\ResultSet;

/**
 * @implements ReadRepositoryInterface<ExampleEntity>
 * @implements WriteRepositoryInterface<ExampleEntity>
 */
final class InMemoryExampleRepository implements
    ReadRepositoryInterface,
    WriteRepositoryInterface
{
    /**
     * @var array<int|string, ExampleEntity>
     */
    private array $items = [];

    public function name(): RepositoryName
    {
        return new RepositoryName('example');
    }

    public function managedType(): string
    {
        return ExampleEntity::class;
    }

    public function findById(string|int $identifier): ?object
    {
        return $this->items[$identifier] ?? null;
    }

    public function query(QueryInterface $query): ResultSetInterface
    {
        return new ResultSet(array_values($this->items));
    }

    public function save(object $object): void
    {
        $this->items[$object->id] = $object;
    }

    public function remove(object $object): void
    {
        unset($this->items[$object->id]);
    }
}
