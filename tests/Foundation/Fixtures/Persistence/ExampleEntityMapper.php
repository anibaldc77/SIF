<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Persistence;

use Sif\Foundation\Contracts\MapperInterface;
use Sif\Foundation\Persistence\StorageRecord;

/**
 * @implements MapperInterface<ExampleEntity>
 */
final readonly class ExampleEntityMapper implements MapperInterface
{
    public function hydrate(StorageRecord $record): object
    {
        return new ExampleEntity(
            id: (int) $record->get('id'),
            name: (string) $record->get('name'),
            active: (bool) $record->get('active'),
        );
    }

    public function extract(object $object): StorageRecord
    {
        return new StorageRecord([
            'id' => $object->id,
            'name' => $object->name,
            'active' => $object->active,
        ]);
    }
}
