<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Contract;

use Sif\Builder\Cli\CommandResult;
use Sif\Builder\Cli\Input\CommandInput;

interface CommandInterface
{
    public function name(): string;

    public function description(): string;

    public function execute(CommandInput $input): CommandResult;
}
