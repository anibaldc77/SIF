<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Compilation;

use Sif\Foundation\Persistence\Pdo\Ast\PdoSelectQuery;

interface PdoSelectQueryCompiler
{
    public function compile(PdoSelectQuery $query): PdoCompiledQuery;
}
