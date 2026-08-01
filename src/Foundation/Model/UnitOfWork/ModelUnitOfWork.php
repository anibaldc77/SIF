<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\UnitOfWork;

use Sif\Foundation\Contracts\UnitOfWorkInterface;
use Sif\Foundation\Model\BaseModel;
use Sif\Foundation\Persistence\UnitOfWorkState;

final readonly class ModelUnitOfWork
{
    public function __construct(private UnitOfWorkInterface $unitOfWork)
    {
    }

    public function persist(BaseModel $model): void
    {
        if (!$model->isPersisted()) {
            $this->unitOfWork->registerNew($model);

            return;
        }

        if ($model->isDirty()) {
            $this->unitOfWork->registerDirty($model);
        }
    }

    public function remove(BaseModel $model): void
    {
        $this->unitOfWork->registerRemoved($model);
    }

    /** @param iterable<BaseModel> $models */
    public function persistAll(iterable $models): void
    {
        foreach ($models as $model) {
            $this->persist($model);
        }
    }

    public function commit(): void
    {
        $this->unitOfWork->commit();
    }

    public function clear(): void
    {
        $this->unitOfWork->clear();
    }

    public function state(): UnitOfWorkState
    {
        return $this->unitOfWork->state();
    }

    public function isEmpty(): bool
    {
        return $this->unitOfWork->isEmpty();
    }
}
