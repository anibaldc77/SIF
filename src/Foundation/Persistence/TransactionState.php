<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

enum TransactionState: string
{
    case Idle = 'idle';
    case Active = 'active';
    case Committed = 'committed';
    case RolledBack = 'rolled_back';
}
