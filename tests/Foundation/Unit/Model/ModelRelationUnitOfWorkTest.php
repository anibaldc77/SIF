<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\QueryInterface;
use Sif\Foundation\Contracts\ReadRepositoryInterface;
use Sif\Foundation\Contracts\ResultSetInterface;
use Sif\Foundation\Contracts\TransactionManagerInterface;
use Sif\Foundation\Model\BaseModel;
use Sif\Foundation\Model\Metadata\ModelAttributeCast;
use Sif\Foundation\Model\Metadata\ModelAttributeDefinition;
use Sif\Foundation\Model\Metadata\ModelAttributeName;
use Sif\Foundation\Model\Metadata\ModelIdentityDefinition;
use Sif\Foundation\Model\Metadata\ModelMetadata;
use Sif\Foundation\Model\Query\ModelQueryService;
use Sif\Foundation\Model\Relation\ModelRelationDefinition;
use Sif\Foundation\Model\Relation\ModelRelationLoader;
use Sif\Foundation\Model\Relation\ModelRelationSynchronizer;
use Sif\Foundation\Model\Relation\ModelRelationType;
use Sif\Foundation\Model\State\ModelAttributeState;
use Sif\Foundation\Model\UnitOfWork\ModelUnitOfWork;
use Sif\Foundation\Persistence\InMemoryUnitOfWork;
use Sif\Foundation\Persistence\RepositoryName;
use Sif\Foundation\Persistence\ResultSet;
use Sif\Foundation\Persistence\TransactionState;

final class ModelRelationUnitOfWorkTest extends TestCase
{
    public function testExplicitHasManyRelationLoadBuildsCriteria(): void
    {
        $ownerMetadata = self::ownerMetadata();
        $relatedMetadata = self::relatedMetadata();
        $owner = self::owner($ownerMetadata);
        $related = self::related($relatedMetadata, 10);
        $repository = new RelationRepository([$related]);
        $service = new ModelQueryService($repository);
        $loader = new ModelRelationLoader(static fn (string $modelClass): ModelQueryService => $service);
        $definition = new ModelRelationDefinition(
            'children',
            $ownerMetadata,
            $relatedMetadata,
            ModelRelationType::HasMany,
            ['id'],
            ['owner_id'],
        );

        $loaded = $loader->load($owner, $definition);

        self::assertIsArray($loaded);
        self::assertCount(1, $loaded);
        self::assertNotNull($repository->lastQuery);
        self::assertSame('owner_id', $repository->lastQuery->criteria()->all()[0]->field());
        self::assertSame(7, $repository->lastQuery->criteria()->all()[0]->value());
    }

    public function testSynchronizerCopiesOwnerKeyToRelatedModel(): void
    {
        $ownerMetadata = self::ownerMetadata();
        $relatedMetadata = self::relatedMetadata();
        $owner = self::owner($ownerMetadata);
        $related = self::related($relatedMetadata, 10, null);
        $definition = new ModelRelationDefinition(
            'children',
            $ownerMetadata,
            $relatedMetadata,
            ModelRelationType::HasMany,
            ['id'],
            ['owner_id'],
        );

        (new ModelRelationSynchronizer())->synchronize($owner, $related, $definition);

        self::assertSame(7, $related->get('owner_id'));
        self::assertTrue($related->isDirty('owner_id'));
    }

    public function testModelUnitOfWorkClassifiesNewAndDirtyModels(): void
    {
        $transactionManager = new RelationTransactionManager();
        $inner = new CapturingUnitOfWork($transactionManager);
        $models = new ModelUnitOfWork($inner);
        $new = self::related(self::relatedMetadata(), 11, 7);
        $dirty = self::related(self::relatedMetadata(), 12, 7);
        $dirty->markPersisted();
        $dirty->set('name', 'changed');

        $models->persist($new);
        $models->persist($dirty);
        $models->commit();

        self::assertSame(1, $inner->capturedNew);
        self::assertSame(1, $inner->capturedDirty);
        self::assertSame(1, $transactionManager->transactions);
        self::assertTrue($models->isEmpty());
    }

    private static function ownerMetadata(): ModelMetadata
    {
        return new ModelMetadata(
            RelationOwnerModel::class,
            'owners',
            [
                new ModelAttributeDefinition(new ModelAttributeName('id'), ModelAttributeCast::Integer, false, false, false, true),
                new ModelAttributeDefinition(new ModelAttributeName('name'), ModelAttributeCast::String, false, true),
            ],
            new ModelIdentityDefinition([new ModelAttributeName('id')]),
        );
    }

    private static function relatedMetadata(): ModelMetadata
    {
        return new ModelMetadata(
            RelationChildModel::class,
            'children',
            [
                new ModelAttributeDefinition(new ModelAttributeName('id'), ModelAttributeCast::Integer, false, false, false, true),
                new ModelAttributeDefinition(new ModelAttributeName('owner_id'), ModelAttributeCast::Integer, true, true),
                new ModelAttributeDefinition(new ModelAttributeName('name'), ModelAttributeCast::String, false, true),
            ],
            new ModelIdentityDefinition([new ModelAttributeName('id')]),
        );
    }

    private static function owner(ModelMetadata $metadata): RelationOwnerModel
    {
        $state = new ModelAttributeState($metadata);
        $state->hydrate(['id' => 7, 'name' => 'owner']);
        $model = new RelationOwnerModel($metadata, $state);
        $model->markPersisted();

        return $model;
    }

    private static function related(ModelMetadata $metadata, int $id, ?int $ownerId = 7): RelationChildModel
    {
        $state = new ModelAttributeState($metadata);
        $state->hydrate(['id' => $id, 'owner_id' => $ownerId, 'name' => 'child']);

        return new RelationChildModel($metadata, $state);
    }
}

final class RelationOwnerModel extends BaseModel
{
}

final class RelationChildModel extends BaseModel
{
}

/** @implements ReadRepositoryInterface<RelationChildModel> */
final class RelationRepository implements ReadRepositoryInterface
{
    public ?QueryInterface $lastQuery = null;

    /** @param list<RelationChildModel> $items */
    public function __construct(private array $items)
    {
    }

    public function name(): RepositoryName
    {
        return new RepositoryName('children');
    }

    public function managedType(): string
    {
        return RelationChildModel::class;
    }

    public function findById(string|int $identifier): ?object
    {
        return $this->items[0] ?? null;
    }

    public function query(QueryInterface $query): ResultSetInterface
    {
        $this->lastQuery = $query;

        return new ResultSet($this->items);
    }
}

final class RelationTransactionManager implements TransactionManagerInterface
{
    public int $transactions = 0;

    public function transactional(callable $operation): mixed
    {
        ++$this->transactions;

        return $operation();
    }

    public function state(): TransactionState
    {
        return TransactionState::Idle;
    }

    public function depth(): int
    {
        return 0;
    }
}

final class CapturingUnitOfWork extends InMemoryUnitOfWork
{
    public int $capturedNew = 0;
    public int $capturedDirty = 0;

    protected function apply(\Sif\Foundation\Persistence\ChangeSet $changes): void
    {
        $this->capturedNew = count($changes->newObjects());
        $this->capturedDirty = count($changes->dirtyObjects());
    }
}
