<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Ast;

use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameter;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameterBag;

final readonly class PdoSqlComparisonPredicate implements PdoSqlPredicate
{
    public function __construct(
        private PdoSqlIdentifier $field,
        private PdoSqlComparisonOperator $operator,
        private PdoSqlParameter $parameter,
    ) {
    }

    public function field(): PdoSqlIdentifier
    {
        return $this->field;
    }

    public function operator(): PdoSqlComparisonOperator
    {
        return $this->operator;
    }

    public function parameter(): PdoSqlParameter
    {
        return $this->parameter;
    }

    public function parameters(): PdoSqlParameterBag
    {
        return new PdoSqlParameterBag([$this->parameter]);
    }
}
