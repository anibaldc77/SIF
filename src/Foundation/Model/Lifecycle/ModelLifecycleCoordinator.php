<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Lifecycle;

use DateTimeInterface;
use Sif\Foundation\Audit\AuditAction;
use Sif\Foundation\Audit\AuditLevel;
use Sif\Foundation\Audit\AuditPayload;
use Sif\Foundation\Contracts\AuditServiceInterface;
use Sif\Foundation\Contracts\EventDispatcherInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Model\Audit\ModelAuditPayloadFactory;
use Sif\Foundation\Model\BaseModel;
use Sif\Foundation\Model\Persistence\ModelRepositoryBridge;
use Sif\Foundation\Model\SoftDelete\ModelSoftDeleteManager;

/** @template T of BaseModel */
final readonly class ModelLifecycleCoordinator
{
    /**
     * @param ModelRepositoryBridge<T> $bridge
     * @param ModelSoftDeleteManager<T>|null $softDeletes
     */
    public function __construct(
        private ModelRepositoryBridge $bridge,
        private EventDispatcherInterface $events,
        private AuditServiceInterface $audit,
        private ModelLifecycleHookCollection $hooks = new ModelLifecycleHookCollection(),
        private ModelAuditPayloadFactory $payloads = new ModelAuditPayloadFactory(),
        private ?ModelSoftDeleteManager $softDeletes = null,
    ) {
    }

    /** @param T $model */
    public function save(BaseModel $model, ExecutionContextInterface $context): void
    {
        $operation = $model->isPersisted()
            ? ModelLifecycleOperation::Update
            : ModelLifecycleOperation::Create;

        $this->perform(
            $model,
            $operation,
            $context,
            fn (): null => $this->saveModel($model),
        );
    }

    /** @param T $model */
    public function delete(BaseModel $model, ExecutionContextInterface $context): void
    {
        $this->perform(
            $model,
            ModelLifecycleOperation::Delete,
            $context,
            fn (): null => $this->deleteModel($model),
        );
    }

    /** @param T $model */
    public function softDelete(
        BaseModel $model,
        ExecutionContextInterface $context,
        ?DateTimeInterface $at = null,
    ): void {
        $manager = $this->requireSoftDeletes();
        $this->perform(
            $model,
            ModelLifecycleOperation::SoftDelete,
            $context,
            static function () use ($manager, $model, $at): null {
                $manager->delete($model, $at);

                return null;
            },
        );
    }

    /** @param T $model */
    public function restore(BaseModel $model, ExecutionContextInterface $context): void
    {
        $manager = $this->requireSoftDeletes();
        $this->perform(
            $model,
            ModelLifecycleOperation::Restore,
            $context,
            static function () use ($manager, $model): null {
                $manager->restore($model);

                return null;
            },
        );
    }

    /**
     * @param T $model
     * @param callable(): null $operation
     */
    private function perform(
        BaseModel $model,
        ModelLifecycleOperation $lifecycle,
        ExecutionContextInterface $context,
        callable $operation,
    ): void {
        $before = $model->state()->original();
        $candidate = $model->state()->current();
        $changes = $this->payloads->changes($before, $candidate);

        $beforeEvent = new ModelLifecycleEvent(
            $model,
            $lifecycle,
            ModelLifecyclePhase::Before,
            $context,
            $before,
            $candidate,
            $changes,
        );
        $this->hooks->before($beforeEvent);
        $this->events->dispatch($beforeEvent);

        $operation();

        $after = $model->state()->current();
        $effectiveChanges = $this->payloads->changes($before, $after);
        $afterEvent = new ModelLifecycleEvent(
            $model,
            $lifecycle,
            ModelLifecyclePhase::After,
            $context,
            $before,
            $after,
            $effectiveChanges,
        );
        $this->hooks->after($afterEvent);
        $this->events->dispatch($afterEvent);

        $this->audit->record(
            context: $context,
            action: new AuditAction('model.' . $lifecycle->value),
            level: AuditLevel::Informational,
            subject: $this->payloads->subject($model),
            payload: new AuditPayload([
                'model' => $model::class,
                'repository' => $model->metadata()->repositoryName(),
                'operation' => $lifecycle->value,
            ]),
            before: $this->payloads->payload($before),
            after: $this->payloads->payload($after),
            changes: $this->payloads->payload($effectiveChanges),
            tags: ['basemodel', 'lifecycle', $lifecycle->value],
        );
    }

    /** @param T $model */
    private function saveModel(BaseModel $model): null
    {
        $this->bridge->save($model);

        return null;
    }

    /** @param T $model */
    private function deleteModel(BaseModel $model): null
    {
        $this->bridge->delete($model);

        return null;
    }

    /** @return ModelSoftDeleteManager<T> */
    private function requireSoftDeletes(): ModelSoftDeleteManager
    {
        if ($this->softDeletes === null) {
            throw new \LogicException('Soft-delete lifecycle operations require a configured manager.');
        }

        return $this->softDeletes;
    }
}
