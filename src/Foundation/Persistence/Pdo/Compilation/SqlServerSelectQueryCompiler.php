<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Compilation;

use Sif\Foundation\Persistence\Pdo\Ast\PdoSelectQuery;
use Sif\Foundation\Persistence\Pdo\Exception\PdoQueryCompilationException;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistencePlatform;

final class SqlServerSelectQueryCompiler extends AbstractPdoSelectQueryCompiler
{
    protected function supportedPlatform(): PdoPersistencePlatform
    {
        return PdoPersistencePlatform::sqlserver();
    }

    protected function compilePagination(string $sql, PdoSelectQuery $query, bool $hasOrderBy): string
    {
        $pagination = $query->pagination();
        if ($pagination === null) {
            return $sql;
        }
        if (!$hasOrderBy) {
            throw new PdoQueryCompilationException('SQL Server pagination requires an explicit ORDER BY clause.');
        }

        return $sql . ' OFFSET ' . $pagination->offset()
            . ' ROWS FETCH NEXT ' . $pagination->limit() . ' ROWS ONLY';
    }
}
