<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Contract;

use Sif\Builder\Cli\Input\ArgvInput;
use Sif\Builder\Cli\Input\CommandInput;

interface ArgumentParserInterface
{
    public function parse(ArgvInput $input): CommandInput;
}
