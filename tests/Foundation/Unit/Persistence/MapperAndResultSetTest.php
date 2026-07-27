<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Persistence;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Exceptions\InvalidPageResultException;
use Sif\Foundation\Exceptions\InvalidStorageRecordKeyException;
use Sif\Foundation\Exceptions\UnsupportedStorageRecordValueException;
use Sif\Foundation\Persistence\MappedResultSetFactory;
use Sif\Foundation\Persistence\PageResult;
use Sif\Foundation\Persistence\ResultSet;
use Sif\Foundation\Persistence\StorageRecord;
use Sif\Foundation\Tests\Fixtures\Persistence\ExampleEntity;
use Sif\Foundation\Tests\Fixtures\Persistence\ExampleEntityMapper;

final class MapperAndResultSetTest extends TestCase
{
    public function testStorageRecordPreservesCompatibleValues(): void
    {
        $record = new StorageRecord([
            'id' => 10,
            'name' => 'Example',
            'active' => true,
            'ratio' => 1.5,
            'metadata' => [
                'tags' => ['one', 'two'],
            ],
        ]);

        self::assertFalse($record->isEmpty());
        self::assertTrue($record->has('id'));
        self::assertSame(10, $record->get('id'));
        self::assertSame(
            ['tags' => ['one', 'two']],
            $record->get('metadata'),
        );
    }

    public function testStorageRecordRejectsEmptyKey(): void
    {
        $this->expectException(InvalidStorageRecordKeyException::class);

        new StorageRecord([' ' => 'invalid']);
    }

    public function testStorageRecordRejectsUnsupportedValues(): void
    {
        $this->expectException(
            UnsupportedStorageRecordValueException::class,
        );

        new StorageRecord([
            'object' => new \stdClass(),
        ]);
    }

    public function testStorageRecordRejectsNonFiniteFloat(): void
    {
        $this->expectException(
            UnsupportedStorageRecordValueException::class,
        );

        new StorageRecord([
            'ratio' => INF,
        ]);
    }

    public function testMapperHydratesAndExtractsExplicitly(): void
    {
        $mapper = new ExampleEntityMapper();

        $entity = $mapper->hydrate(
            new StorageRecord([
                'id' => 1,
                'name' => 'Alpha',
                'active' => true,
            ]),
        );

        self::assertInstanceOf(ExampleEntity::class, $entity);
        self::assertSame(1, $entity->id);
        self::assertSame('Alpha', $entity->name);
        self::assertTrue($entity->active);

        $record = $mapper->extract($entity);

        self::assertSame(
            [
                'id' => 1,
                'name' => 'Alpha',
                'active' => true,
            ],
            $record->all(),
        );
    }

    public function testResultSetPreservesOrderAndSupportsIteration(): void
    {
        $items = [
            new ExampleEntity(1, 'First', true),
            new ExampleEntity(2, 'Second', false),
        ];

        $resultSet = new ResultSet($items);

        self::assertSame(2, $resultSet->count());
        self::assertFalse($resultSet->isEmpty());
        self::assertSame($items[0], $resultSet->first());
        self::assertSame($items, $resultSet->all());
        self::assertSame($items, iterator_to_array($resultSet));
    }

    public function testEmptyResultSetHasNoFirstItem(): void
    {
        $resultSet = new ResultSet();

        self::assertTrue($resultSet->isEmpty());
        self::assertSame(0, $resultSet->count());
        self::assertNull($resultSet->first());
        self::assertSame([], $resultSet->all());
    }

    public function testMappedResultSetFactoryHydratesEveryRecord(): void
    {
        $factory = new MappedResultSetFactory(
            new ExampleEntityMapper(),
        );

        $resultSet = $factory->create([
            new StorageRecord([
                'id' => 1,
                'name' => 'One',
                'active' => true,
            ]),
            new StorageRecord([
                'id' => 2,
                'name' => 'Two',
                'active' => false,
            ]),
        ]);

        self::assertSame(2, $resultSet->count());
        self::assertSame('One', $resultSet->all()[0]->name);
        self::assertSame('Two', $resultSet->all()[1]->name);
    }

    public function testPageResultCalculatesMetadata(): void
    {
        $items = new ResultSet([
            new ExampleEntity(1, 'One', true),
            new ExampleEntity(2, 'Two', true),
        ]);

        $page = new PageResult(
            items: $items,
            page: 2,
            perPage: 2,
            totalItems: 5,
        );

        self::assertSame($items, $page->items());
        self::assertSame(2, $page->page());
        self::assertSame(2, $page->perPage());
        self::assertSame(5, $page->totalItems());
        self::assertSame(3, $page->totalPages());
        self::assertTrue($page->hasNextPage());
        self::assertTrue($page->hasPreviousPage());
    }

    public function testEmptyPageResultHasZeroTotalPages(): void
    {
        $page = new PageResult(
            items: new ResultSet(),
            page: 1,
            perPage: 20,
            totalItems: 0,
        );

        self::assertSame(0, $page->totalPages());
        self::assertFalse($page->hasNextPage());
        self::assertFalse($page->hasPreviousPage());
    }

    public function testPageResultRejectsTooManyItems(): void
    {
        $this->expectException(InvalidPageResultException::class);

        new PageResult(
            items: new ResultSet([
                new ExampleEntity(1, 'One', true),
                new ExampleEntity(2, 'Two', true),
            ]),
            page: 1,
            perPage: 1,
            totalItems: 2,
        );
    }

    public function testPageResultRejectsNegativeTotal(): void
    {
        $this->expectException(InvalidPageResultException::class);

        new PageResult(
            items: new ResultSet(),
            page: 1,
            perPage: 10,
            totalItems: -1,
        );
    }
}
