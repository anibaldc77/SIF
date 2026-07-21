<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Contract;

use Sif\Builder\Cli\Input\CommandInput;
use Sif\Builder\Cli\Runtime\EngineExecutionMode;
use Sif\Builder\Engine\BuilderRequest;

interface BuilderRequestFactoryInterface
{
    public function create(CommandInput $input, EngineExecutionMode $mode): BuilderRequest;
}
