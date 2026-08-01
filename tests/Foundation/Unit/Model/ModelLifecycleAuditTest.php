<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Model;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Audit\AuditAction;
use Sif\Foundation\Audit\AuditId;
use Sif\Foundation\Audit\AuditLevel;
use Sif\Foundation\Audit\AuditPayload;
use Sif\Foundation\Audit\AuditRecord;
use Sif\Foundation\Audit\AuditSubject;
use Sif\Foundation\Contracts\AuditServiceInterface;
use Sif\Foundation\Contracts\EventDispatcherInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Contracts\ReadRepositoryInterface;
use Sif\Foundation\Contracts\WriteRepositoryInterface;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ExecutionContext;
use Sif\Foundation\Model\BaseModel;
use Sif\Foundation\Model\Lifecycle\ModelLifecycleCoordinator;
use Sif\Foundation\Model\Lifecycle\ModelLifecycleEvent;
use Sif\Foundation\Model\Lifecycle\ModelLifecycleHookCollection;
use Sif\Foundation\Model\Lifecycle\ModelLifecycleHookInterface;
use Sif\Foundation\Model\Lifecycle\ModelLifecycleOperation;
use Sif\Foundation\Model\Metadata\ModelAttributeCast;
use Sif\Foundation\Model\Metadata\ModelAttributeDefinition;
use Sif\Foundation\Model\Metadata\ModelAttributeName;
use Sif\Foundation\Model\Metadata\ModelIdentityDefinition;
use Sif\Foundation\Model\Metadata\ModelMetadata;
use Sif\Foundation\Model\Persistence\ModelRepositoryBridge;
use Sif\Foundation\Model\State\ModelAttributeState;
use Sif\Foundation\Model\State\ModelSerializer;
use Sif\Foundation\Persistence\RepositoryName;

final class ModelLifecycleAuditTest extends TestCase
{
    public function testSaveRunsHooksEventsAndAuditWithExplicitContext(): void
    {
        $metadata = self::metadata();
        $state = new ModelAttributeState($metadata);
        $state->hydrate(['id' => 1, 'name' => 'before']);
        $model = new LifecycleModel($metadata, $state);
        $model->markPersisted();
        $model->set('name', 'after');

        $repository = new LifecycleRepository();
        $bridge = new ModelRepositoryBridge($metadata, $repository, $repository);
        $events = new RecordingDispatcher();
        $audit = new RecordingAuditService();
        $hook = new RecordingHook();
        $coordinator = new ModelLifecycleCoordinator(
            $bridge,
            $events,
            $audit,
            new ModelLifecycleHookCollection([$hook]),
        );

        $coordinator->save($model, self::context());

        self::assertSame(['before:update', 'after:update'], $hook->calls);
        self::assertCount(2, $events->events);
        self::assertSame('model.update', $audit->action?->value());
        self::assertSame('after', $repository->saved?->get('name'));
        self::assertFalse($model->isDirty());
    }

    private static function metadata(): ModelMetadata
    {
        return new ModelMetadata(
            LifecycleModel::class,
            'lifecycle_models',
            [
                new ModelAttributeDefinition(new ModelAttributeName('id'), ModelAttributeCast::Integer),
                new ModelAttributeDefinition(new ModelAttributeName('name'), ModelAttributeCast::String, fillable: true),
            ],
            new ModelIdentityDefinition([new ModelAttributeName('id')]),
        );
    }

    private static function context(): ExecutionContextInterface
    {
        return new ExecutionContext(
            new ContextId('ctx-12345678'),
            new ContextId('cor-12345678'),
            new DateTimeImmutable('2026-07-31T23:30:00-03:00'),
            new ContextAttributes(),
            actorId: 'tester',
            operation: 'model.update',
            source: 'phpunit',
        );
    }
}

final class LifecycleModel extends BaseModel
{
}

/**
 * @implements ReadRepositoryInterface<LifecycleModel>
 * @implements WriteRepositoryInterface<LifecycleModel>
 */
final class LifecycleRepository implements ReadRepositoryInterface, WriteRepositoryInterface
{
    public ?LifecycleModel $saved = null;

    public function name(): RepositoryName
    {
        return new RepositoryName('lifecycle_models');
    }

    public function managedType(): string
    {
        return LifecycleModel::class;
    }

    public function findById(string|int $identifier): ?object
    {
        return null;
    }

    public function query(\Sif\Foundation\Contracts\QueryInterface $query): \Sif\Foundation\Contracts\ResultSetInterface
    {
        return new \Sif\Foundation\Persistence\ResultSet([]);
    }

    public function save(object $entity): void
    {
        if (!$entity instanceof LifecycleModel) {
            throw new \InvalidArgumentException('Unexpected model type.');
        }

        $this->saved = $entity;
    }

    public function remove(object $entity): void
    {
    }
}

final class RecordingDispatcher implements EventDispatcherInterface
{
    /** @var list<ModelLifecycleEvent> */
    public array $events = [];

    public function dispatch(object $event): object
    {
        if (!$event instanceof ModelLifecycleEvent) {
            throw new \InvalidArgumentException('Unexpected event type.');
        }

        $this->events[] = $event;

        return $event;
    }
}

final class RecordingHook implements ModelLifecycleHookInterface
{
    /** @var list<string> */
    public array $calls = [];

    public function supports(BaseModel $model, ModelLifecycleOperation $operation): bool
    {
        return true;
    }

    public function before(ModelLifecycleEvent $event): void
    {
        $this->calls[] = 'before:' . $event->operation()->value;
    }

    public function after(ModelLifecycleEvent $event): void
    {
        $this->calls[] = 'after:' . $event->operation()->value;
    }
}

final class RecordingAuditService implements AuditServiceInterface
{
    public ?AuditAction $action = null;

    public function record(
        ExecutionContextInterface $context,
        AuditAction $action,
        AuditLevel $level,
        AuditSubject $subject,
        AuditPayload $payload = new AuditPayload(),
        ?AuditPayload $before = null,
        ?AuditPayload $after = null,
        ?AuditPayload $changes = null,
        array $tags = [],
        string $schemaVersion = '1.0',
    ): AuditRecord {
        $this->action = $action;

        return new AuditRecord(
            new AuditId('audit-test'),
            $action,
            $level,
            new DateTimeImmutable('2026-07-31T23:31:00-03:00'),
            $context,
            $subject,
            $payload,
            $before,
            $after,
            $changes,
            $tags,
            $schemaVersion,
        );
    }
}
