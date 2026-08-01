<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Lifecycle;

enum ModelLifecyclePhase: string
{
    case Before = 'before';
    case After = 'after';
}
