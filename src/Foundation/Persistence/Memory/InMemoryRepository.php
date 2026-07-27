<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Memory;

use Closure;
use Sif\Foundation\Contracts\MapperInterface;
use Sif\Foundation\Contracts\PersistenceCapabilityProviderInterface;
use Sif\Foundation\Contracts\QueryInterface;
use Sif\Foundation\Contracts\ReadRepositoryInterface;
use Sif\Foundation\Contracts\ResultSetInterface;
use Sif\Foundation\Contracts\WriteRepositoryInterface;
use Sif\Foundation\Exceptions\RepositoryFailureException;
use Sif\Foundation\Persistence\MappedResultSetFactory;
use Sif\Foundation\Persistence\PersistenceCapabilities;
use Sif\Foundation\Persistence\PersistenceCapability;
use Sif\Foundation\Persistence\RepositoryName;
use Throwable;

/**
 * @template T of object
 * @implements ReadRepositoryInterface<T>
 * @implements WriteRepositoryInterface<T>
 */
final readonly class InMemoryRepository implements
    ReadRepositoryInterface,
    WriteRepositoryInterface,
    PersistenceCapabilityProviderInterface
{
    /**
     * @var Closure(T): (string|int)
     */
    private Closure $identifierResolver;

    /**
     * @param class-string<T> $managedType
     * @param MapperInterface<T> $mapper
     * @param callable(T): (string|int) $identifierResolver
     */
    public function __construct(
        private RepositoryName $name,
        private string $managedType,
        private string $collection,
        private MapperInterface $mapper,
        private InMemoryStorage $storage,
        private InMemoryQueryEvaluator $queryEvaluator,
        callable $identifierResolver,
    ) {
        $this->identifierResolver = Closure::fromCallable(
            $identifierResolver,
        );
    }

    public function name(): RepositoryName
    {
        return $this->name;
    }

    public function managedType(): string
    {
        return $this->managedType;
    }

    public function findById(string|int $identifier): ?object
    {
        $record = $this->storage->get(
            $this->collection,
            $identifier,
        );

        if ($record === null) {
            return null;
        }

        try {
            return $this->mapper->hydrate($record);
        } catch (Throwable $failure) {
            throw new RepositoryFailureException(
                message: sprintf(
                    'Unable to hydrate object from repository "%s".',
                    $this->name->value(),
                ),
                operation: 'repository.find_by_id',
                cause: $failure,
            );
        }
    }

    public function query(QueryInterface $query): ResultSetInterface
    {
        try {
            $records = $this->queryEvaluator->evaluate(
                $this->storage->all($this->collection),
                $query,
            );

            return (new MappedResultSetFactory($this->mapper))
                ->create($records);
        } catch (Throwable $failure) {
            if ($failure instanceof RepositoryFailureException) {
                throw $failure;
            }

            throw new RepositoryFailureException(
                message: sprintf(
                    'Unable to query repository "%s".',
                    $this->name->value(),
                ),
                operation: 'repository.query',
                cause: $failure,
            );
        }
    }

    public function save(object $object): void
    {
        if (!$object instanceof $this->managedType) {
            throw new RepositoryFailureException(
                message: sprintf(
                    'Repository "%s" cannot save object of type "%s".',
                    $this->name->value(),
                    $object::class,
                ),
                operation: 'repository.save',
            );
        }

        try {
            $identifier = ($this->identifierResolver)($object);

            $this->storage->put(
                $this->collection,
                $identifier,
                $this->mapper->extract($object),
            );
        } catch (Throwable $failure) {
            if ($failure instanceof RepositoryFailureException) {
                throw $failure;
            }

            throw new RepositoryFailureException(
                message: sprintf(
                    'Unable to save object in repository "%s".',
                    $this->name->value(),
                ),
                operation: 'repository.save',
                cause: $failure,
            );
        }
    }

    public function remove(object $object): void
    {
        if (!$object instanceof $this->managedType) {
            throw new RepositoryFailureException(
                message: sprintf(
                    'Repository "%s" cannot remove object of type "%s".',
                    $this->name->value(),
                    $object::class,
                ),
                operation: 'repository.remove',
            );
        }

        try {
            $identifier = ($this->identifierResolver)($object);
            $this->storage->remove($this->collection, $identifier);
        } catch (Throwable $failure) {
            if ($failure instanceof RepositoryFailureException) {
                throw $failure;
            }

            throw new RepositoryFailureException(
                message: sprintf(
                    'Unable to remove object from repository "%s".',
                    $this->name->value(),
                ),
                operation: 'repository.remove',
                cause: $failure,
            );
        }
    }

    public function capabilities(): PersistenceCapabilities
    {
        return PersistenceCapabilities::of([
            PersistenceCapability::QueryCriteria,
            PersistenceCapability::Sorting,
            PersistenceCapability::OffsetPagination,
            PersistenceCapability::Projection,
        ]);
    }
}
