<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Ast;

use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;
use Sif\Foundation\Persistence\SortDirection;

final readonly class PdoSqlSortTerm
{
    public function __construct(
        private PdoSqlIdentifier $field,
        private SortDirection $direction,
    ) {
    }

    public function field(): PdoSqlIdentifier
    {
        return $this->field;
    }

    public function direction(): SortDirection
    {
        return $this->direction;
    }
}
