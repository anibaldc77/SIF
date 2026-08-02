<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Value;

enum FirstRunState: string
{
    case Uninitialized = 'uninitialized';
    case Validated = 'validated';
    case Configured = 'configured';
    case Planned = 'planned';
    case Authorized = 'authorized';
    case Executed = 'executed';
    case Completed = 'completed';
    case Failed = 'failed';
}
