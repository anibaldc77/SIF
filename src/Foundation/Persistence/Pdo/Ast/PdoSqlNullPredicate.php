<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Ast;

use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameterBag;

final readonly class PdoSqlNullPredicate implements PdoSqlPredicate
{
    public function __construct(
        private PdoSqlIdentifier $field,
        private bool $negated = false,
    ) {
    }

    public function field(): PdoSqlIdentifier
    {
        return $this->field;
    }

    public function negated(): bool
    {
        return $this->negated;
    }

    public function parameters(): PdoSqlParameterBag
    {
        return new PdoSqlParameterBag();
    }
}
