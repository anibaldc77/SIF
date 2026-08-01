<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Repository;

use Closure;
use Sif\Foundation\Contracts\MapperInterface;
use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoRepositoryDefinitionException;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;
use Sif\Foundation\Persistence\RepositoryName;

/** @template T of object */
final readonly class PdoRepositoryDefinition
{
    /** @var Closure(T): PdoRecordKey */
    private Closure $keyResolver;

    /**
     * @param class-string<T> $managedType
     * @param MapperInterface<T> $mapper
     * @param callable(T): PdoRecordKey $keyResolver
     * @param list<string> $keyColumns
     * @param list<string> $writableColumns
     */
    public function __construct(
        private RepositoryName $name,
        private string $managedType,
        private PdoSqlIdentifier $table,
        private MapperInterface $mapper,
        callable $keyResolver,
        private array $keyColumns,
        private array $writableColumns,
    ) {
        if ($this->keyColumns === []) {
            throw new InvalidPdoRepositoryDefinitionException('PDO repository requires key columns.');
        }

        foreach ($this->keyColumns as $column) {
            $identifier = new PdoSqlIdentifier($column);
            if (count($identifier->segments()) !== 1) {
                throw new InvalidPdoRepositoryDefinitionException('Key columns cannot be qualified.');
            }
        }

        if ($this->writableColumns === []) {
            throw new InvalidPdoRepositoryDefinitionException('PDO repository requires writable columns.');
        }

        $seen = [];
        foreach ($this->writableColumns as $column) {
            $identifier = new PdoSqlIdentifier($column);
            if (count($identifier->segments()) !== 1) {
                throw new InvalidPdoRepositoryDefinitionException('Writable columns cannot be qualified.');
            }
            if (isset($seen[$identifier->value()])) {
                throw new InvalidPdoRepositoryDefinitionException('Writable columns must be unique.');
            }
            $seen[$identifier->value()] = true;
        }

        $this->keyResolver = Closure::fromCallable($keyResolver);
    }

    public function name(): RepositoryName { return $this->name; }
    /** @return class-string<T> */ public function managedType(): string { return $this->managedType; }
    public function table(): PdoSqlIdentifier { return $this->table; }
    /** @return MapperInterface<T> */ public function mapper(): MapperInterface { return $this->mapper; }
    /** @return list<string> */ public function keyColumns(): array { return $this->keyColumns; }
    /** @return list<string> */ public function writableColumns(): array { return $this->writableColumns; }

    /** @param T $object */
    public function keyOf(object $object): PdoRecordKey
    {
        if (!$object instanceof $this->managedType) {
            throw new InvalidPdoRepositoryDefinitionException('Object type is not managed by this PDO repository.');
        }

        return ($this->keyResolver)($object);
    }
}
