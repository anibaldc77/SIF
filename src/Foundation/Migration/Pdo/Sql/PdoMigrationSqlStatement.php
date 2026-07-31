<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Sql;

use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationSqlStatementException;

final readonly class PdoMigrationSqlStatement
{
    private string $sql;

    /** @var array<string, bool|float|int|string|null> */
    private array $parameters;

    /** @param iterable<string, bool|float|int|string|null> $parameters */
    public function __construct(string $sql, iterable $parameters = [])
    {
        $sql = trim($sql);
        if ($sql === '') {
            throw new InvalidPdoMigrationSqlStatementException('Migration SQL statement cannot be empty.');
        }
        if (str_contains($sql, "\0")) {
            throw new InvalidPdoMigrationSqlStatementException('Migration SQL statement cannot contain null bytes.');
        }

        $normalized = [];
        foreach ($parameters as $name => $value) {
            if (!is_string($name) || preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $name) !== 1) {
                throw new InvalidPdoMigrationSqlStatementException(
                    'Migration SQL parameter names must use safe named-parameter vocabulary.',
                );
            }
            if (array_key_exists($name, $normalized)) {
                throw new InvalidPdoMigrationSqlStatementException(
                    sprintf('Duplicate migration SQL parameter "%s".', $name),
                );
            }
            $normalized[$name] = $value;
        }

        $this->sql = $sql;
        $this->parameters = $normalized;
    }

    public function sql(): string
    {
        return $this->sql;
    }

    /** @return array<string, bool|float|int|string|null> */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /** @return array{sql: string, parameter_names: list<string>, parameter_count: int} */
    public function summary(): array
    {
        return [
            'sql' => $this->sql,
            'parameter_names' => array_keys($this->parameters),
            'parameter_count' => count($this->parameters),
        ];
    }
}
