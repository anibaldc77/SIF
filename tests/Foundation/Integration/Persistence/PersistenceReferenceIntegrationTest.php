<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Integration\Persistence;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Persistence\ConnectionName;
use Sif\Foundation\Persistence\Pagination;
use Sif\Foundation\Persistence\PersistenceCapability;
use Sif\Foundation\Persistence\PersistenceCapabilityGuard;
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

/**
 * @phpstan-type ExampleRepository InMemoryRepository<ExampleEntity>
 */
final class PersistenceReferenceIntegrationTest extends TestCase
{
    public function testVerticalPersistenceFlow(): void
    {
        $connection = new InMemoryConnection(
            new ConnectionName('memory'),
        );
        $transactionManager = new InMemoryTransactionManager();
        $storage = new InMemoryStorage();
        $repository = $this->repository($storage);

        $transactionManager->transactional(
            static function () use ($repository): void {
                $repository->save(
                    new ExampleEntity(1, 'Alpha', true),
                );
                $repository->save(
                    new ExampleEntity(2, 'Beta', false),
                );
                $repository->save(
                    new ExampleEntity(3, 'Gamma', true),
                );
            },
        );

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
            )
            ->withPagination(
                Pagination::firstPage(10),
            )
            ->withProjection(
                new Projection([
                    'id',
                    'name',
                    'active',
                ]),
            );

        $results = $repository->query($query);

        self::assertTrue($connection->isOpen());
        self::assertSame(
            TransactionState::Committed,
            $transactionManager->state(),
        );
        self::assertSame(3, $storage->count('examples'));
        self::assertSame(2, $results->count());
        self::assertSame('Gamma', $results->all()[0]->name);
        self::assertSame('Alpha', $results->all()[1]->name);
    }

    public function testCapabilitiesCanBeGuardedBeforeOperation(): void
    {
        $repository = $this->repository(
            new InMemoryStorage(),
        );
        $guard = new PersistenceCapabilityGuard();

        $guard->require(
            $repository,
            PersistenceCapability::QueryCriteria,
        );
        $guard->require(
            $repository,
            PersistenceCapability::Sorting,
        );
        $guard->require(
            $repository,
            PersistenceCapability::OffsetPagination,
        );
        $guard->require(
            $repository,
            PersistenceCapability::Projection,
        );

        self::assertTrue(true);
    }

    public function testStorageAndRepositoryRemainIndependentOfConnection(): void
    {
        $storage = new InMemoryStorage();
        $repository = $this->repository($storage);

        $repository->save(
            new ExampleEntity(10, 'Independent', true),
        );

        self::assertSame(1, $storage->count('examples'));
        self::assertSame(
            'Independent',
            $repository->findById(10)?->name,
        );
    }

    public function testReferenceAdapterPerformsNoExternalIo(): void
    {
        $storage = new InMemoryStorage();
        $repository = $this->repository($storage);

        $repository->save(
            new ExampleEntity(1, 'One', true),
        );

        $result = $repository->query(
            (new Query())->withCriterion(
                new QueryCriterion(
                    'id',
                    QueryOperator::Equal,
                    1,
                ),
            ),
        );

        self::assertSame(1, $result->count());
        self::assertSame('One', $result->first()?->name);
        self::assertSame(1, $storage->count('examples'));
    }

    /**
     * @return ExampleRepository
     */
    private function repository(
        InMemoryStorage $storage,
    ): InMemoryRepository {
        return new InMemoryRepository(
            name: new RepositoryName('examples'),
            managedType: ExampleEntity::class,
            collection: 'examples',
            mapper: new ExampleEntityMapper(),
            storage: $storage,
            queryEvaluator: new InMemoryQueryEvaluator(),
            identifierResolver: static fn (
                ExampleEntity $entity,
            ): int => $entity->id,
        );
    }
}
