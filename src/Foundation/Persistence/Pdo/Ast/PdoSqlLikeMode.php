<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Ast;

enum PdoSqlLikeMode: string
{
    case Contains = 'contains';
    case StartsWith = 'starts_with';
    case EndsWith = 'ends_with';
}
