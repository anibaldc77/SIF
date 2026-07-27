<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

enum SortDirection: string
{
    case Ascending = 'asc';
    case Descending = 'desc';
}
