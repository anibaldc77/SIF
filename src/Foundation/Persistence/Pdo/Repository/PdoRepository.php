<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Repository;

use Sif\Foundation\Contracts\PersistenceCapabilityProviderInterface;
use Sif\Foundation\Contracts\QueryInterface;
use Sif\Foundation\Contracts\ReadRepositoryInterface;
use Sif\Foundation\Contracts\ResultSetInterface;
use Sif\Foundation\Contracts\WriteRepositoryInterface;
use Sif\Foundation\Exceptions\RepositoryFailureException;
use Sif\Foundation\Persistence\MappedResultSetFactory;
use Sif\Foundation\Persistence\Pagination;
use Sif\Foundation\Persistence\Pdo\Compilation\PdoCompiledQuery;
use Sif\Foundation\Persistence\Pdo\Compilation\PdoSelectQueryCompiler;
use Sif\Foundation\Persistence\Pdo\Execution\PdoPreparedStatementExecutor;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameter;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameterBag;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistencePlatform;
use Sif\Foundation\Persistence\Pdo\Translation\PdoQueryTranslator;
use Sif\Foundation\Persistence\PersistenceCapabilities;
use Sif\Foundation\Persistence\Projection;
use Sif\Foundation\Persistence\Query;
use Sif\Foundation\Persistence\QueryCriterion;
use Sif\Foundation\Persistence\QueryOperator;
use Sif\Foundation\Persistence\RepositoryName;
use Sif\Foundation\Persistence\ResultSet;
use Sif\Foundation\Persistence\StorageRecord;
use Throwable;

/**
 * @template T of object
 * @implements ReadRepositoryInterface<T>
 * @implements WriteRepositoryInterface<T>
 */
final readonly class PdoRepository implements ReadRepositoryInterface, WriteRepositoryInterface, PersistenceCapabilityProviderInterface, PdoManagedRepository
{
    /** @param PdoRepositoryDefinition<T> $definition */
    public function __construct(
        private PdoRepositoryDefinition $definition,
        private PdoQueryTranslator $translator,
        private PdoSelectQueryCompiler $compiler,
        private PdoPreparedStatementExecutor $executor,
        private PdoPersistencePlatform $platform,
        private PersistenceCapabilities $capabilities,
    ) {
    }

    public function name(): RepositoryName { return $this->definition->name(); }
    public function managedType(): string { return $this->definition->managedType(); }
    public function capabilities(): PersistenceCapabilities { return $this->capabilities; }

    public function supports(object $object): bool
    {
        $managedType = $this->definition->managedType();

        return $object instanceof $managedType;
    }

    public function saveObject(object $object): void
    {
        if (!$this->supports($object)) {
            throw new RepositoryFailureException('Object type is not managed by this PDO repository.', 'repository.save');
        }

        /** @var T $object */
        $this->save($object);
    }

    public function removeObject(object $object): void
    {
        if (!$this->supports($object)) {
            throw new RepositoryFailureException('Object type is not managed by this PDO repository.', 'repository.remove');
        }

        /** @var T $object */
        $this->remove($object);
    }

    /** @return T|null */
    public function findById(string|int $identifier): ?object
    {
        $columns = $this->definition->keyColumns();
        if (count($columns) !== 1) {
            throw new RepositoryFailureException('Composite-key PDO repositories require findByKey().', 'repository.find_by_id');
        }

        return $this->findByKey(PdoRecordKey::single($columns[0], $identifier));
    }

    /** @return T|null */
    public function findByKey(PdoRecordKey $key): ?object
    {
        $query = new Query(projection: new Projection());
        foreach ($key->values() as $column => $value) {
            $query = $query->withCriterion(new QueryCriterion($column, QueryOperator::Equal, $value));
        }
        $query = $query->withPagination(new Pagination(1, 1));

        $result = $this->query($query)->first();
        if ($result === null) {
            return null;
        }

        $managedType = $this->definition->managedType();
        if (!$result instanceof $managedType) {
            throw new RepositoryFailureException('PDO repository returned an unexpected managed type.', 'repository.find_by_key');
        }

        return $result;
    }

    public function query(QueryInterface $query): ResultSetInterface
    {
        if (!$query instanceof Query) {
            throw new RepositoryFailureException('PDO repository requires the canonical Query implementation.', 'repository.query');
        }

        try {
            $ast = $this->translator->translate($this->definition->table(), $query);
            $compiled = $this->compiler->compile($ast);
            $records = $this->executor->execute($compiled)->records()->all();

            return (new MappedResultSetFactory($this->definition->mapper()))->create($records);
        } catch (Throwable $failure) {
            if ($failure instanceof RepositoryFailureException) {
                throw $failure;
            }
            throw new RepositoryFailureException(
                sprintf('Unable to query PDO repository "%s".', $this->name()->value()),
                'repository.query',
                $failure,
            );
        }
    }

    public function save(object $object): void
    {
        $this->assertManaged($object, 'repository.save');

        try {
            $record = $this->definition->mapper()->extract($object);
            $key = $this->definition->keyOf($object);
            $exists = $this->recordExists($key);
            $compiled = $exists ? $this->compileUpdate($record, $key) : $this->compileInsert($record);
            if ($compiled !== null) {
                $this->executor->execute($compiled);
            }
        } catch (Throwable $failure) {
            if ($failure instanceof RepositoryFailureException) {
                throw $failure;
            }
            throw new RepositoryFailureException(
                sprintf('Unable to save object in PDO repository "%s".', $this->name()->value()),
                'repository.save',
                $failure,
            );
        }
    }

    public function remove(object $object): void
    {
        $this->assertManaged($object, 'repository.remove');

        try {
            $this->executor->execute($this->compileDelete($this->definition->keyOf($object)));
        } catch (Throwable $failure) {
            if ($failure instanceof RepositoryFailureException) {
                throw $failure;
            }
            throw new RepositoryFailureException(
                sprintf('Unable to remove object from PDO repository "%s".', $this->name()->value()),
                'repository.remove',
                $failure,
            );
        }
    }

    private function recordExists(PdoRecordKey $key): bool
    {
        return $this->findByKey($key) !== null;
    }

    private function compileInsert(StorageRecord $record): PdoCompiledQuery
    {
        $columns = [];
        $placeholders = [];
        $parameters = [];
        foreach ($this->definition->writableColumns() as $column) {
            if (!$record->has($column)) {
                continue;
            }
            $identifier = new PdoSqlIdentifier($column);
            $columns[] = $identifier->quoted($this->platform);
            $parameter = new PdoSqlParameter('write_' . $column, $record->get($column));
            $placeholders[] = $parameter->placeholder();
            $parameters[] = $parameter;
        }
        if ($columns === []) {
            throw new RepositoryFailureException('PDO repository has no writable values to insert.', 'repository.save');
        }

        return new PdoCompiledQuery(
            sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                $this->definition->table()->quoted($this->platform),
                implode(', ', $columns),
                implode(', ', $placeholders),
            ),
            new PdoSqlParameterBag($parameters),
        );
    }

    private function compileUpdate(StorageRecord $record, PdoRecordKey $key): ?PdoCompiledQuery
    {
        $assignments = [];
        $parameters = [];
        foreach ($this->definition->writableColumns() as $column) {
            if (!$record->has($column) || array_key_exists($column, $key->values())) {
                continue;
            }
            $parameter = new PdoSqlParameter('write_' . $column, $record->get($column));
            $assignments[] = (new PdoSqlIdentifier($column))->quoted($this->platform) . ' = ' . $parameter->placeholder();
            $parameters[] = $parameter;
        }
        if ($assignments === []) {
            return null;
        }
        [$where, $keyParameters] = $this->compileKey($key, 'key_');

        return new PdoCompiledQuery(
            sprintf(
                'UPDATE %s SET %s WHERE %s',
                $this->definition->table()->quoted($this->platform),
                implode(', ', $assignments),
                $where,
            ),
            new PdoSqlParameterBag([...$parameters, ...$keyParameters]),
        );
    }

    private function compileDelete(PdoRecordKey $key): PdoCompiledQuery
    {
        [$where, $parameters] = $this->compileKey($key, 'key_');

        return new PdoCompiledQuery(
            sprintf('DELETE FROM %s WHERE %s', $this->definition->table()->quoted($this->platform), $where),
            new PdoSqlParameterBag($parameters),
        );
    }

    /** @return array{0: string, 1: list<PdoSqlParameter>} */
    private function compileKey(PdoRecordKey $key, string $prefix): array
    {
        $clauses = [];
        $parameters = [];
        foreach ($key->values() as $column => $value) {
            $parameter = new PdoSqlParameter($prefix . $column, $value);
            $clauses[] = (new PdoSqlIdentifier($column))->quoted($this->platform) . ' = ' . $parameter->placeholder();
            $parameters[] = $parameter;
        }
        return [implode(' AND ', $clauses), $parameters];
    }

    private function assertManaged(object $object, string $operation): void
    {
        $managedType = $this->definition->managedType();
        if (!$object instanceof $managedType) {
            throw new RepositoryFailureException(
                sprintf('PDO repository "%s" cannot manage object of type "%s".', $this->name()->value(), $object::class),
                $operation,
            );
        }
    }
}
