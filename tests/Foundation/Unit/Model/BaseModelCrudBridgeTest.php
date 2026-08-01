<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\QueryInterface;
use Sif\Foundation\Contracts\ReadRepositoryInterface;
use Sif\Foundation\Contracts\ResultSetInterface;
use Sif\Foundation\Contracts\WriteRepositoryInterface;
use Sif\Foundation\Model\BaseModel;
use Sif\Foundation\Model\Metadata\ModelAttributeDefinition;
use Sif\Foundation\Model\Metadata\ModelAttributeName;
use Sif\Foundation\Model\Metadata\ModelIdentityDefinition;
use Sif\Foundation\Model\Metadata\ModelMetadata;
use Sif\Foundation\Model\Persistence\BaseModelMapper;
use Sif\Foundation\Model\Persistence\ModelRepositoryBridge;
use Sif\Foundation\Model\State\ModelAttributeState;
use Sif\Foundation\Model\State\ModelHydrator;
use Sif\Foundation\Persistence\RepositoryName;
use Sif\Foundation\Persistence\ResultSet;
use Sif\Foundation\Persistence\StorageRecord;

final class BaseModelCrudBridgeTest extends TestCase
{
    public function testMapperHydratesAndExtractsModelState(): void
    {
        $metadata = self::metadata();
        $mapper = self::mapper($metadata);

        $model = $mapper->hydrate(new StorageRecord(['id' => 7, 'name' => 'Alice']));

        self::assertInstanceOf(CrudFixtureModel::class, $model);
        self::assertTrue($model->isPersisted());
        self::assertSame(['id' => 7, 'name' => 'Alice'], $mapper->extract($model)->all());
    }

    public function testSaveSynchronizesDirtyStateAfterRepositorySuccess(): void
    {
        $metadata = self::metadata();
        $model = self::model($metadata, ['id' => 7, 'name' => 'Alice']);
        $model->set('name', 'Bob');
        $repository = new CrudFixtureRepository($model);
        $bridge = new ModelRepositoryBridge($metadata, $repository, $repository);

        $bridge->save($model);

        self::assertTrue($model->isPersisted());
        self::assertFalse($model->isDirty());
        self::assertSame(1, $repository->saveCount);
    }

    public function testRefreshReplacesStateFromRepository(): void
    {
        $metadata = self::metadata();
        $model = self::model($metadata, ['id' => 7, 'name' => 'Old']);
        $fresh = self::model($metadata, ['id' => 7, 'name' => 'Fresh']);
        $fresh->markPersisted();
        $repository = new CrudFixtureRepository($fresh);
        $bridge = new ModelRepositoryBridge($metadata, $repository, $repository);

        $bridge->refresh($model);

        self::assertSame('Fresh', $model->get('name'));
        self::assertFalse($model->isDirty());
    }

    public function testDeleteRequiresIdentityAndMarksModelDeleted(): void
    {
        $metadata = self::metadata();
        $model = self::model($metadata, ['id' => 7, 'name' => 'Alice']);
        $repository = new CrudFixtureRepository($model);
        $bridge = new ModelRepositoryBridge($metadata, $repository, $repository);

        $bridge->delete($model);

        self::assertTrue($model->isDeleted());
        self::assertFalse($model->isPersisted());
        self::assertSame(1, $repository->removeCount);
    }

    public function testCompositeIdentityCanUseExplicitFinder(): void
    {
        $metadata = self::compositeMetadata();
        $model = self::model($metadata, ['tenant_id' => 2, 'id' => 7, 'name' => 'Alice']);
        $repository = new CrudFixtureRepository($model, 'composite_users');
        $bridge = new ModelRepositoryBridge(
            $metadata,
            $repository,
            $repository,
            static fn (array $identity): ?CrudFixtureModel => $identity === ['tenant_id' => 2, 'id' => 7] ? $model : null,
        );

        $found = $bridge->findByIdentity(['tenant_id' => 2, 'id' => 7]);

        self::assertSame($model, $found);
    }

    private static function metadata(): ModelMetadata
    {
        return new ModelMetadata(
            CrudFixtureModel::class,
            'users',
            [
                new ModelAttributeDefinition(new ModelAttributeName('id'), nullable: false),
                new ModelAttributeDefinition(new ModelAttributeName('name'), nullable: false, fillable: true),
            ],
            new ModelIdentityDefinition([new ModelAttributeName('id')]),
        );
    }

    private static function compositeMetadata(): ModelMetadata
    {
        return new ModelMetadata(
            CrudFixtureModel::class,
            'composite_users',
            [
                new ModelAttributeDefinition(new ModelAttributeName('tenant_id'), nullable: false),
                new ModelAttributeDefinition(new ModelAttributeName('id'), nullable: false),
                new ModelAttributeDefinition(new ModelAttributeName('name'), nullable: false, fillable: true),
            ],
            new ModelIdentityDefinition([
                new ModelAttributeName('tenant_id'),
                new ModelAttributeName('id'),
            ]),
        );
    }

    /** @param array<string, mixed> $values */
    private static function model(ModelMetadata $metadata, array $values): CrudFixtureModel
    {
        return new CrudFixtureModel($metadata, (new ModelHydrator())->hydrate($metadata, $values));
    }

    private static function mapper(ModelMetadata $metadata): BaseModelMapper
    {
        return new BaseModelMapper(
            $metadata,
            static fn (ModelMetadata $modelMetadata, ModelAttributeState $state): CrudFixtureModel => new CrudFixtureModel($modelMetadata, $state),
        );
    }
}

final class CrudFixtureModel extends BaseModel
{
}

/**
 * @implements ReadRepositoryInterface<CrudFixtureModel>
 * @implements WriteRepositoryInterface<CrudFixtureModel>
 */
final class CrudFixtureRepository implements ReadRepositoryInterface, WriteRepositoryInterface
{
    public int $saveCount = 0;
    public int $removeCount = 0;

    public function __construct(
        private ?CrudFixtureModel $stored,
        private string $repositoryName = 'users',
    ) {
    }

    public function name(): RepositoryName
    {
        return new RepositoryName($this->repositoryName);
    }

    public function managedType(): string
    {
        return CrudFixtureModel::class;
    }

    public function findById(string|int $identifier): ?object
    {
        return $this->stored;
    }

    public function query(QueryInterface $query): ResultSetInterface
    {
        return new ResultSet($this->stored === null ? [] : [$this->stored]);
    }

    public function save(object $object): void
    {
        if (!$object instanceof CrudFixtureModel) {
            throw new \InvalidArgumentException('Unsupported fixture object.');
        }
        $this->stored = $object;
        ++$this->saveCount;
    }

    public function remove(object $object): void
    {
        if (!$object instanceof CrudFixtureModel) {
            throw new \InvalidArgumentException('Unsupported fixture object.');
        }
        $this->stored = null;
        ++$this->removeCount;
    }
}
