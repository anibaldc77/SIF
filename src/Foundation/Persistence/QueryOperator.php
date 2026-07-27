<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

enum QueryOperator: string
{
    case Equal = 'eq';
    case NotEqual = 'neq';
    case GreaterThan = 'gt';
    case GreaterThanOrEqual = 'gte';
    case LessThan = 'lt';
    case LessThanOrEqual = 'lte';
    case In = 'in';
    case NotIn = 'not_in';
    case IsNull = 'is_null';
    case IsNotNull = 'is_not_null';
    case Contains = 'contains';
    case StartsWith = 'starts_with';
    case EndsWith = 'ends_with';
}
