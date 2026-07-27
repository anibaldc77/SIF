<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

enum UnitOfWorkState: string
{
    case Clean = 'clean';
    case Pending = 'pending';
    case Committing = 'committing';
    case Committed = 'committed';
    case Failed = 'failed';
}
