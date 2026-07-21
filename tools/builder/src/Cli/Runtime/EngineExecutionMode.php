<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Runtime;

enum EngineExecutionMode: string
{
    case BUILD = 'build';
    case ANALYSIS_ONLY = 'analysis_only';
}
