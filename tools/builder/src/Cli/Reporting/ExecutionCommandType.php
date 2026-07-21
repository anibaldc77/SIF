<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Reporting;

enum ExecutionCommandType: string
{
    case BUILD = 'build';
    case VALIDATE = 'validate';
}
