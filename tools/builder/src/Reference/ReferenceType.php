<?php

declare(strict_types=1);

namespace Sif\Builder\Reference;

enum ReferenceType: string
{
    case REFERENCE = 'reference';
    case IMPLEMENTS = 'implements';
    case EXTENDS = 'extends';
    case SUPERSEDES = 'supersedes';
    case RELATED = 'related';
}
