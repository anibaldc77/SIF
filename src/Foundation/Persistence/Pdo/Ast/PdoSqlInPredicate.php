<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Ast;

use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoQueryAstException;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameterBag;

final readonly class PdoSqlInPredicate implements PdoSqlPredicate
{
    public function __construct(
        private PdoSqlIdentifier $field,
        private PdoSqlParameterBag $parameterBag,
        private bool $negated = false,
    ) {
        if ($this->parameterBag->count() === 0) {
            throw new InvalidPdoQueryAstException('IN predicates require at least one parameter.');
        }
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
        return $this->parameterBag;
    }
}
