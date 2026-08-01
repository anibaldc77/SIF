<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Ast;

enum PdoSqlComparisonOperator: string
{
    case Equal = '=';
    case NotEqual = '<>';
    case GreaterThan = '>';
    case GreaterThanOrEqual = '>=';
    case LessThan = '<';
    case LessThanOrEqual = '<=';
}
