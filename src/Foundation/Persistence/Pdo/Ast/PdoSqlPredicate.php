<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Ast;

use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameterBag;

interface PdoSqlPredicate
{
    public function parameters(): PdoSqlParameterBag;
}
