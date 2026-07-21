<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Contract;

use Sif\Builder\Cli\CommandResult;
use Sif\Builder\Cli\Input\ArgvInput;

interface CliApplicationInterface
{
    public function run(ArgvInput $input): CommandResult;
}
