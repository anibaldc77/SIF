<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Contract;

use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Reporting\ExecutionCommandType;
use Sif\Builder\Engine\BuilderResult;

interface BuilderExitCodeMapperInterface
{
    public function map(ExecutionCommandType $commandType, BuilderResult $result): ExitCode;
}
