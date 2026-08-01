<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Persistence;

use Closure;
use Sif\Foundation\Contracts\ReadRepositoryInterface;
use Sif\Foundation\Contracts\WriteRepositoryInterface;
use Sif\Foundation\Model\BaseModel;
use Sif\Foundation\Model\Exceptions\IncompleteModelIdentityException;
use Sif\Foundation\Model\Exceptions\ModelPersistenceException;
use Sif\Foundation\Model\Exceptions\ModelRepositoryMismatchException;
use Sif\Foundation\Model\Metadata\ModelMetadata;
use Throwable;

/** @template T of BaseModel */
final readonly class ModelRepositoryBridge
{
    /** @var Closure(array<string, mixed>): (T|null)|null */
    private ?Closure $compositeFinder;

    /**
     * @param ReadRepositoryInterface<T> $reader
     * @param WriteRepositoryInterface<T> $writer
     * @param callable(array<string, mixed>): (T|null)|null $compositeFinder
     */
    public function __construct(
        private ModelMetadata $metadata,
        private ReadRepositoryInterface $reader,
        private WriteRepositoryInterface $writer,
        ?callable $compositeFinder = null,
    ) {
        $this->assertRepository($reader->name()->value(), $reader->managedType());
        $this->assertRepository($writer->name()->value(), $writer->managedType());
        $this->compositeFinder = $compositeFinder === null ? null : Closure::fromCallable($compositeFinder);
    }

    /** @return T|null */
    public function find(string|int $identifier): ?BaseModel
    {
        return $this->assertModelOrNull($this->reader->findById($identifier));
    }

    /** @param array<string, mixed> $identity @return T|null */
    public function findByIdentity(array $identity): ?BaseModel
    {
        $names = $this->metadata->identity()->names();
        if (count($names) === 1) {
            $name = $names[0];
            if (!array_key_exists($name, $identity) || !is_string($identity[$name]) && !is_int($identity[$name])) {
                throw new IncompleteModelIdentityException('Simple model identity requires one string or integer value.');
            }

            return $this->find($identity[$name]);
        }

        $this->assertCompleteIdentityArray($identity);
        if ($this->compositeFinder === null) {
            throw new ModelPersistenceException('Composite identity lookup requires an explicit repository finder.');
        }

        return $this->assertModelOrNull(($this->compositeFinder)($identity));
    }

    /** @param T $model */
    public function save(BaseModel $model): void
    {
        $this->assertModel($model);
        try {
            $this->writer->save($model);
            $model->markPersisted();
        } catch (Throwable $failure) {
            throw new ModelPersistenceException('Unable to save BaseModel through its repository.', 0, $failure);
        }
    }

    /** @param T $model */
    public function delete(BaseModel $model): void
    {
        $this->assertModel($model);
        $model->requireCompleteIdentity();
        try {
            $this->writer->remove($model);
            $model->markDeleted();
        } catch (Throwable $failure) {
            throw new ModelPersistenceException('Unable to delete BaseModel through its repository.', 0, $failure);
        }
    }

    /** @param T $model */
    public function refresh(BaseModel $model): void
    {
        $this->assertModel($model);
        $model->requireCompleteIdentity();
        $fresh = $this->findByIdentity($model->identityValues());
        if ($fresh === null) {
            throw new ModelPersistenceException('Unable to refresh a model that no longer exists.');
        }

        $model->replaceFromStorage($fresh->toStorageArray());
    }

    private function assertRepository(string $name, string $managedType): void
    {
        if ($name !== $this->metadata->repositoryName() || $managedType !== $this->metadata->modelClass()) {
            throw new ModelRepositoryMismatchException(sprintf(
                'Repository "%s" managing "%s" is incompatible with model metadata.',
                $name,
                $managedType,
            ));
        }
    }

    /** @param T $model */
    private function assertModel(BaseModel $model): void
    {
        if ($model::class !== $this->metadata->modelClass()) {
            throw new ModelRepositoryMismatchException(sprintf(
                'Bridge for "%s" cannot manage "%s".',
                $this->metadata->modelClass(),
                $model::class,
            ));
        }
    }

    /** @return T|null */
    private function assertModelOrNull(?object $model): ?BaseModel
    {
        if ($model === null) {
            return null;
        }
        if (!$model instanceof BaseModel || $model::class !== $this->metadata->modelClass()) {
            throw new ModelRepositoryMismatchException('Repository returned an incompatible model instance.');
        }

        /** @var T $model */
        return $model;
    }

    /** @param array<string, mixed> $identity */
    private function assertCompleteIdentityArray(array $identity): void
    {
        foreach ($this->metadata->identity()->names() as $name) {
            if (!array_key_exists($name, $identity) || $identity[$name] === null) {
                throw new IncompleteModelIdentityException(sprintf('Missing identity attribute "%s".', $name));
            }
        }
    }
}
