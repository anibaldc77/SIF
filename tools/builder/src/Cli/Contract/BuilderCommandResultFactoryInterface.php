<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Contract;

use Sif\Builder\Cli\CommandResult;
use Sif\Builder\Cli\Input\CommandInput;
use Sif\Builder\Cli\Reporting\ExecutionCommandType;
use Sif\Builder\Engine\BuilderResult;

interface BuilderCommandResultFactoryInterface
{
    public function create(
        ExecutionCommandType $commandType,
        CommandInput $input,
        BuilderResult $builderResult,
    ): CommandResult;
}
