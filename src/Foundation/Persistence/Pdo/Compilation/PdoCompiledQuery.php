<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Compilation;

use Sif\Foundation\Persistence\Pdo\Exception\PdoQueryCompilationException;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameterBag;

final readonly class PdoCompiledQuery
{
    public function __construct(
        private string $sql,
        private PdoSqlParameterBag $parameters,
    ) {
        if (trim($this->sql) === '') {
            throw new PdoQueryCompilationException('Compiled SQL cannot be empty.');
        }
    }

    public function sql(): string
    {
        return $this->sql;
    }

    public function parameters(): PdoSqlParameterBag
    {
        return $this->parameters;
    }

    /** @return array{sql: string, parameter_count: int} */
    public function summary(): array
    {
        return ['sql' => $this->sql, 'parameter_count' => $this->parameters->count()];
    }
}
