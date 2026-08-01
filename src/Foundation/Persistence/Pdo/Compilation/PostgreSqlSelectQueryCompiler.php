<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Compilation;

use Sif\Foundation\Persistence\Pdo\Ast\PdoSelectQuery;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistencePlatform;

final class PostgreSqlSelectQueryCompiler extends AbstractPdoSelectQueryCompiler
{
    protected function supportedPlatform(): PdoPersistencePlatform
    {
        return PdoPersistencePlatform::postgresql();
    }

    protected function compilePagination(string $sql, PdoSelectQuery $query, bool $hasOrderBy): string
    {
        $pagination = $query->pagination();
        return $pagination === null
            ? $sql
            : $sql . ' LIMIT ' . $pagination->limit() . ' OFFSET ' . $pagination->offset();
    }
}
