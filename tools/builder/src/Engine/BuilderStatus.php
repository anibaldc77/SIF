<?php

declare(strict_types=1);

namespace Sif\Builder\Engine;

enum BuilderStatus: string
{
    case SUCCEEDED = 'succeeded';
    case SUCCEEDED_WITH_DIAGNOSTICS = 'succeeded_with_diagnostics';
    case FAILED = 'failed';
}
