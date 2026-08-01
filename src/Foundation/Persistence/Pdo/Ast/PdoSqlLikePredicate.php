<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Ast;

use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameter;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameterBag;

final readonly class PdoSqlLikePredicate implements PdoSqlPredicate
{
    public function __construct(
        private PdoSqlIdentifier $field,
        private PdoSqlLikeMode $mode,
        private PdoSqlParameter $parameter,
        private string $escapeCharacter = '\\',
    ) {
    }

    public function field(): PdoSqlIdentifier
    {
        return $this->field;
    }

    public function mode(): PdoSqlLikeMode
    {
        return $this->mode;
    }

    public function parameter(): PdoSqlParameter
    {
        return $this->parameter;
    }

    public function escapeCharacter(): string
    {
        return $this->escapeCharacter;
    }

    public function parameters(): PdoSqlParameterBag
    {
        return new PdoSqlParameterBag([$this->parameter]);
    }
}
