<?php

declare(strict_types=1);

namespace Sif\Builder\Cli;

enum ExitCode: int
{
    case SUCCESS = 0;
    case INVALID_USAGE = 2;
    case CONFIGURATION_ERROR = 3;
    case VALIDATION_FAILED = 4;
    case GENERATION_FAILED = 5;
    case PARTIAL_SUCCESS = 6;
    case INTERNAL_ERROR = 10;

    public function isSuccess(): bool
    {
        return $this === self::SUCCESS;
    }
}
