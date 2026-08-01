<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\SoftDelete;

use DateTimeImmutable;
use DateTimeInterface;
use Sif\Foundation\Model\BaseModel;
use Sif\Foundation\Model\Exceptions\ModelSoftDeleteException;
use Sif\Foundation\Model\Persistence\ModelRepositoryBridge;

/** @template T of BaseModel */
final readonly class ModelSoftDeleteManager
{
    /** @param ModelRepositoryBridge<T> $bridge */
    public function __construct(private ModelRepositoryBridge $bridge)
    {
    }

    /** @param T $model */
    public function delete(BaseModel $model, ?DateTimeInterface $at = null): void
    {
        $attribute = $this->deletedAt($model);
        $model->setManagedAttribute($attribute, $at ?? new DateTimeImmutable());
        $this->bridge->save($model);
    }

    /** @param T $model */
    public function restore(BaseModel $model): void
    {
        $attribute = $this->deletedAt($model);
        $model->setManagedAttribute($attribute, null);
        $this->bridge->save($model);
    }

    /** @param T $model */
    public function forceDelete(BaseModel $model): void
    {
        $this->deletedAt($model);
        $this->bridge->delete($model);
    }

    private function deletedAt(BaseModel $model): string
    {
        $attribute = $model->metadata()->deletedAt();
        if ($attribute === null) {
            throw new ModelSoftDeleteException(sprintf(
                'Model "%s" does not declare soft-delete metadata.',
                $model::class,
            ));
        }

        return $attribute->value();
    }
}
