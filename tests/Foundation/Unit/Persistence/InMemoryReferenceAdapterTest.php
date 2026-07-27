<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Persistence;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Sif\Foundation\Exceptions\NestedTransactionNotSupportedException;
use Sif\Foundation\Exceptions\RepositoryFailureException;
use Sif\Foundation\Persistence\ConnectionName;
use Sif\Foundation\Persistence\Pagination;
use Sif\Foundation\Persistence\PersistenceCapability;
use Sif\Foundation\Persistence\Projection;
use Sif\Foundation\Persistence\Query;
use Sif\Foundation\Persistence\QueryCriterion;
use Sif\Foundation\Persistence\QueryOperator;
use Sif\Foundation\Persistence\RepositoryName;
use Sif\Foundation\Persistence\SortDirection;
use Sif\Foundation\Persistence\SortField;
use Sif\Foundation\Persistence\TransactionState;
use Sif\Foundation\Persistence\Memory\InMemoryConnection;
use Sif\Foundation\Persistence\Memory\InMemoryQueryEvaluator;
use Sif\Foundation\Persistence\Memory\InMemoryRepository;
use Sif\Foundation\Persistence\Memory\InMemoryStorage;
use Sif\Foundation\Persistence\Memory\InMemoryTransactionManager;
use Sif\Foundation\Tests\Fixtures\Persistence\ExampleEntity;
use Sif\Foundation\Tests\Fixtures\Persistence\ExampleEntityMapper;

final class InMemoryReferenceAdapterTest extends TestCase
{
    public function testConnectionLifecycleAndCapabilities(): void
    {
        $connection = new InMemoryConnection(
            new ConnectionName('memory'),
        );

        self::assertTrue($connection->isOpen());
        self::assertTrue(
            $connection->capabilities()->supports(
                PersistenceCapability::Transactions,
            ),
        );
        self::assertTrue(
            $connection->capabilities()->supports(
                PersistenceCapability::QueryCriteria,
            ),
        );

        $connection->close();
        self::assertFalse($connection->isOpen());

        $connection->reopen();
        self::assertTrue($connection->isOpen());
    }

    public function testTransactionManagerCommitsAndPreservesReturnValue(): void
    {
        $manager = new InMemoryTransactionManager();

        $result = $manager->transactional(
            static fn (): string => 'ok',
        );

        self::assertSame('ok', $result);
        self::assertSame(TransactionState::Committed, $manager->state());
        self::assertSame(0, $manager->depth());
        self::assertTrue(
            $manager->capabilities()->supports(
                PersistenceCapability::Transactions,
            ),
        );
    }

    public function testTransactionManagerRejectsNestedTransaction(): void
    {
        $manager = new InMemoryTransactionManager();

        $this->expectException(
            NestedTransactionNotSupportedException::class,
        );

        $manager->transactional(
            static function () use ($manager): void {
                $manager->transactional(
                    static fn (): string => 'nested',
                );
            },
        );
    }

    public function testRepositorySavesFindsAndRemovesObjects(): void
    {
        $repository = $this->repository();

        $entity = new ExampleEntity(1, 'Alpha', true);

        $repository->save($entity);

        $found = $repository->findById(1);

        self::assertInstanceOf(ExampleEntity::class, $found);
        self::assertSame(1, $found->id);
        self::assertSame('Alpha', $found->name);

        $repository->remove($entity);

        self::assertNull($repository->findById(1));
    }

    public function testRepositoryRejectsUnsupportedObjectType(): void
    {
        $repository = $this->repository();
        $method = new ReflectionMethod($repository, 'save');

        $this->expectException(RepositoryFailureException::class);

        $method->invoke($repository, new \stdClass());
    }

    public function testQueryCriteriaAndSortingAreApplied(): void
    {
        $repository = $this->seededRepository();

        $query = (new Query())
            ->withCriterion(
                new QueryCriterion(
                    'active',
                    QueryOperator::Equal,
                    true,
                ),
            )
            ->withSortField(
                new SortField(
                    'name',
                    SortDirection::Descending,
                ),
            );

        $results = $repository->query($query);

        self::assertSame(2, $results->count());
        self::assertSame('Gamma', $results->all()[0]->name);
        self::assertSame('Alpha', $results->all()[1]->name);
    }

    public function testQuerySupportsMembershipAndStringOperators(): void
    {
        $repository = $this->seededRepository();

        $query = (new Query())
            ->withCriterion(
                new QueryCriterion(
                    'id',
                    QueryOperator::In,
                    [1, 3],
                ),
            )
            ->withCriterion(
                new QueryCriterion(
                    'name',
                    QueryOperator::EndsWith,
                    'a',
                ),
            );

        $results = $repository->query($query);

        self::assertSame(2, $results->count());
        self::assertSame([1, 3], array_map(
            static fn (ExampleEntity $entity): int => $entity->id,
            $results->all(),
        ));
    }

    public function testPaginationIsAppliedAfterSorting(): void
    {
        $repository = $this->seededRepository();

        $query = (new Query())
            ->withSortField(new SortField('id'))
            ->withPagination(new Pagination(page: 2, perPage: 1));

        $results = $repository->query($query);

        self::assertSame(1, $results->count());
        self::assertSame(2, $results->first()?->id);
    }

    public function testProjectionFeedsMapperWithRequestedFieldsOnly(): void
    {
        $repository = $this->seededRepository();

        $query = (new Query())
            ->withCriterion(
                new QueryCriterion(
                    'id',
                    QueryOperator::Equal,
                    1,
                ),
            )
            ->withProjection(
                new Projection(['id', 'name']),
            );

        $result = $repository->query($query)->first();

        self::assertInstanceOf(ExampleEntity::class, $result);
        self::assertSame(1, $result->id);
        self::assertSame('Alpha', $result->name);
        self::assertFalse($result->active);
    }

    public function testRepositoryDeclaresSupportedCapabilities(): void
    {
        $repository = $this->repository();

        self::assertTrue(
            $repository->capabilities()->supports(
                PersistenceCapability::QueryCriteria,
            ),
        );
        self::assertTrue(
            $repository->capabilities()->supports(
                PersistenceCapability::Sorting,
            ),
        );
        self::assertTrue(
            $repository->capabilities()->supports(
                PersistenceCapability::OffsetPagination,
            ),
        );
        self::assertTrue(
            $repository->capabilities()->supports(
                PersistenceCapability::Projection,
            ),
        );
        self::assertFalse(
            $repository->capabilities()->supports(
                PersistenceCapability::StreamingResults,
            ),
        );
    }

    /**
     * @return InMemoryRepository<ExampleEntity>
     */
    private function repository(): InMemoryRepository
    {
        return new InMemoryRepository(
            name: new RepositoryName('examples'),
            managedType: ExampleEntity::class,
            collection: 'examples',
            mapper: new ExampleEntityMapper(),
            storage: new InMemoryStorage(),
            queryEvaluator: new InMemoryQueryEvaluator(),
            identifierResolver: static fn (
                ExampleEntity $entity,
            ): int => $entity->id,
        );
    }

    /**
     * @return InMemoryRepository<ExampleEntity>
     */
    private function seededRepository(): InMemoryRepository
    {
        $repository = $this->repository();

        $repository->save(new ExampleEntity(1, 'Alpha', true));
        $repository->save(new ExampleEntity(2, 'Beta', false));
        $repository->save(new ExampleEntity(3, 'Gamma', true));

        return $repository;
    }
}
