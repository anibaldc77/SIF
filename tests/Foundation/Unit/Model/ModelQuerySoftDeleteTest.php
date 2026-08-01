<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Model;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\QueryInterface;
use Sif\Foundation\Contracts\ReadRepositoryInterface;
use Sif\Foundation\Contracts\ResultSetInterface;
use Sif\Foundation\Contracts\WriteRepositoryInterface;
use Sif\Foundation\Model\BaseModel;
use Sif\Foundation\Model\Metadata\ModelAttributeCast;
use Sif\Foundation\Model\Metadata\ModelAttributeDefinition;
use Sif\Foundation\Model\Metadata\ModelAttributeName;
use Sif\Foundation\Model\Metadata\ModelIdentityDefinition;
use Sif\Foundation\Model\Metadata\ModelMetadata;
use Sif\Foundation\Model\Persistence\ModelRepositoryBridge;
use Sif\Foundation\Model\Query\ModelQuery;
use Sif\Foundation\Model\Query\ModelQueryService;
use Sif\Foundation\Model\SoftDelete\ModelSoftDeleteManager;
use Sif\Foundation\Model\State\ModelAttributeState;
use Sif\Foundation\Persistence\QueryOperator;
use Sif\Foundation\Persistence\RepositoryName;
use Sif\Foundation\Persistence\ResultSet;

final class ModelQuerySoftDeleteTest extends TestCase
{
    public function testDefaultQueryAddsSoftDeleteCriterion(): void
    {
        $metadata = self::metadata();
        $query = ModelQuery::for($metadata)->where('name', QueryOperator::Equal, 'Alice');

        $criteria = $query->persistenceQuery()->criteria()->all();

        self::assertCount(2, $criteria);
        self::assertSame('deleted_at', $criteria[1]->field());
        self::assertSame(QueryOperator::IsNull, $criteria[1]->operator());
    }

    public function testOnlyTrashedUsesNotNullCriterion(): void
    {
        $query = ModelQuery::for(self::metadata())->onlyTrashed();
        $criteria = $query->persistenceQuery()->criteria()->all();

        self::assertSame(QueryOperator::IsNotNull, $criteria[0]->operator());
    }

    public function testQueryServiceReturnsPage(): void
    {
        $metadata = self::metadata();
        $model = self::model($metadata);
        $repository = new ModelQueryRepository([$model]);
        $service = new ModelQueryService($repository);

        $page = $service->paginate(ModelQuery::for($metadata), 1, 10);

        self::assertCount(1, $page);
        self::assertFalse($page->hasNextPage());
    }

    public function testSoftDeleteAndRestorePersistThroughBridge(): void
    {
        $metadata = self::metadata();
        $model = self::model($metadata);
        $repository = new ModelQueryRepository([$model]);
        $bridge = new ModelRepositoryBridge($metadata, $repository, $repository);
        $manager = new ModelSoftDeleteManager($bridge);

        $manager->delete($model, new DateTimeImmutable('2026-07-31T23:30:00+00:00'));
        self::assertInstanceOf(DateTimeImmutable::class, $model->get('deleted_at'));

        $manager->restore($model);
        self::assertNull($model->get('deleted_at'));
        self::assertSame(2, $repository->saveCount);
    }

    private static function metadata(): ModelMetadata
    {
        return new ModelMetadata(
            QueryModel::class,
            'query_models',
            [
                new ModelAttributeDefinition(new ModelAttributeName('id'), ModelAttributeCast::Integer, false, false, false, true),
                new ModelAttributeDefinition(new ModelAttributeName('name'), ModelAttributeCast::String, false, true),
                new ModelAttributeDefinition(new ModelAttributeName('deleted_at'), ModelAttributeCast::ImmutableDateTime, true, false, false, true),
            ],
            new ModelIdentityDefinition([new ModelAttributeName('id')]),
            deletedAt: new ModelAttributeName('deleted_at'),
        );
    }

    private static function model(ModelMetadata $metadata): QueryModel
    {
        $state = new ModelAttributeState($metadata);
        $state->hydrate(['id' => 1, 'name' => 'Alice', 'deleted_at' => null]);
        $model = new QueryModel($metadata, $state);
        $model->markPersisted();

        return $model;
    }
}

final class QueryModel extends BaseModel
{
}

/**
 * @implements ReadRepositoryInterface<QueryModel>
 * @implements WriteRepositoryInterface<QueryModel>
 */
final class ModelQueryRepository implements ReadRepositoryInterface, WriteRepositoryInterface
{
    /** @param list<QueryModel> $items */
    public function __construct(private array $items, public int $saveCount = 0)
    {
    }

    public function name(): RepositoryName
    {
        return new RepositoryName('query_models');
    }

    public function managedType(): string
    {
        return QueryModel::class;
    }

    public function findById(string|int $identifier): ?object
    {
        return $this->items[0] ?? null;
    }

    public function query(QueryInterface $query): ResultSetInterface
    {
        return new ResultSet($this->items);
    }

    public function save(object $object): void
    {
        ++$this->saveCount;
    }

    public function remove(object $object): void
    {
    }
}
