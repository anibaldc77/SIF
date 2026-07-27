<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Persistence;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Exceptions\InvalidRepositoryNameException;
use Sif\Foundation\Persistence\Query;
use Sif\Foundation\Persistence\RepositoryName;
use Sif\Foundation\Persistence\UnitOfWorkState;
use Sif\Foundation\Tests\Fixtures\Persistence\ExampleEntity;
use Sif\Foundation\Tests\Fixtures\Persistence\InMemoryExampleRepository;
use Sif\Foundation\Tests\Fixtures\Persistence\InMemoryTransactionManager;
use Sif\Foundation\Tests\Fixtures\Persistence\RecordingUnitOfWork;

final class RepositoryAndUnitOfWorkTest extends TestCase
{
    public function testRepositoryNamePreservesOpaqueValue(): void
    {
        $name = new RepositoryName('documents');

        self::assertSame('documents', $name->value());
        self::assertSame('documents', (string) $name);
        self::assertTrue($name->equals(new RepositoryName('documents')));
        self::assertFalse($name->equals(new RepositoryName('cases')));
    }

    public function testRepositoryNameRejectsEmptyValue(): void
    {
        $this->expectException(InvalidRepositoryNameException::class);

        new RepositoryName(' ');
    }

    public function testRepositoryMetadataIsExplicit(): void
    {
        $repository = new InMemoryExampleRepository();

        self::assertSame('example', $repository->name()->value());
        self::assertSame(
            ExampleEntity::class,
            $repository->managedType(),
        );
    }

    public function testRepositorySaveFindQueryAndRemoveAreExplicit(): void
    {
        $repository = new InMemoryExampleRepository();
        $first = new ExampleEntity(1, 'One', true);
        $second = new ExampleEntity(2, 'Two', false);

        $repository->save($first);
        $repository->save($second);

        self::assertSame($first, $repository->findById(1));
        self::assertSame($second, $repository->findById(2));
        self::assertSame(2, $repository->query(new Query())->count());

        $repository->remove($first);

        self::assertNull($repository->findById(1));
        self::assertSame(1, $repository->query(new Query())->count());
    }

    public function testUnitOfWorkTracksNewDirtyAndRemovedObjects(): void
    {
        $unitOfWork = new RecordingUnitOfWork(
            new InMemoryTransactionManager(),
        );

        $new = new ExampleEntity(1, 'New', true);
        $dirty = new ExampleEntity(2, 'Dirty', true);
        $removed = new ExampleEntity(3, 'Removed', false);

        $unitOfWork->registerNew($new);
        $unitOfWork->registerDirty($dirty);
        $unitOfWork->registerRemoved($removed);

        $changes = $unitOfWork->changes();

        self::assertSame([$new], $changes->newObjects());
        self::assertSame([$dirty], $changes->dirtyObjects());
        self::assertSame([$removed], $changes->removedObjects());
        self::assertSame(3, $changes->count());
        self::assertFalse($changes->isEmpty());
        self::assertSame(UnitOfWorkState::Pending, $unitOfWork->state());
    }

    public function testNewObjectIsNotAlsoTrackedAsDirty(): void
    {
        $unitOfWork = new RecordingUnitOfWork(
            new InMemoryTransactionManager(),
        );

        $entity = new ExampleEntity(1, 'New', true);

        $unitOfWork->registerNew($entity);
        $unitOfWork->registerDirty($entity);

        self::assertSame([$entity], $unitOfWork->changes()->newObjects());
        self::assertSame([], $unitOfWork->changes()->dirtyObjects());
    }

    public function testRemovedObjectSupersedesNewAndDirtyTracking(): void
    {
        $unitOfWork = new RecordingUnitOfWork(
            new InMemoryTransactionManager(),
        );

        $entity = new ExampleEntity(1, 'Entity', true);

        $unitOfWork->registerNew($entity);
        $unitOfWork->registerDirty($entity);
        $unitOfWork->registerRemoved($entity);

        self::assertSame([], $unitOfWork->changes()->newObjects());
        self::assertSame([], $unitOfWork->changes()->dirtyObjects());
        self::assertSame([$entity], $unitOfWork->changes()->removedObjects());
    }

    public function testCommitAppliesChangeSetInsideTransactionAndClearsTracking(): void
    {
        $transactionManager = new InMemoryTransactionManager();
        $unitOfWork = new RecordingUnitOfWork($transactionManager);
        $entity = new ExampleEntity(1, 'One', true);

        $unitOfWork->registerNew($entity);
        $unitOfWork->commit();

        self::assertSame(UnitOfWorkState::Committed, $unitOfWork->state());
        self::assertTrue($unitOfWork->isEmpty());
        self::assertCount(1, $unitOfWork->applied());
        self::assertSame([$entity], $unitOfWork->applied()[0]->newObjects());
    }

    public function testEmptyCommitIsAValidNoOp(): void
    {
        $unitOfWork = new RecordingUnitOfWork(
            new InMemoryTransactionManager(),
        );

        $unitOfWork->commit();

        self::assertSame(UnitOfWorkState::Committed, $unitOfWork->state());
        self::assertTrue($unitOfWork->isEmpty());
        self::assertSame([], $unitOfWork->applied());
    }

    public function testClearResetsTrackedObjectsAndState(): void
    {
        $unitOfWork = new RecordingUnitOfWork(
            new InMemoryTransactionManager(),
        );

        $unitOfWork->registerNew(
            new ExampleEntity(1, 'One', true),
        );

        $unitOfWork->clear();

        self::assertTrue($unitOfWork->isEmpty());
        self::assertSame(UnitOfWorkState::Clean, $unitOfWork->state());
    }

    public function testCommitFailurePreservesTrackedChangesAndMarksFailed(): void
    {
        $failure = new RuntimeException('commit failed');

        $transactionManager = new class($failure) implements \Sif\Foundation\Contracts\TransactionManagerInterface
        {
            public function __construct(
                private readonly RuntimeException $failure,
            ) {
            }

            public function transactional(callable $operation): mixed
            {
                throw $this->failure;
            }

            public function state(): \Sif\Foundation\Persistence\TransactionState
            {
                return \Sif\Foundation\Persistence\TransactionState::RolledBack;
            }

            public function depth(): int
            {
                return 0;
            }
        };

        $unitOfWork = new RecordingUnitOfWork($transactionManager);
        $entity = new ExampleEntity(1, 'One', true);
        $unitOfWork->registerNew($entity);

        $this->expectExceptionObject($failure);

        try {
            $unitOfWork->commit();
        } finally {
            self::assertSame(UnitOfWorkState::Failed, $unitOfWork->state());
            self::assertFalse($unitOfWork->isEmpty());
            self::assertSame([$entity], $unitOfWork->changes()->newObjects());
        }
    }
}
