<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Contract;

use Sif\Builder\Cli\Runtime\EngineExecutionMode;
use Sif\Builder\Engine\Contract\BuilderEngineInterface;

interface BuilderEngineFactoryInterface
{
    public function create(EngineExecutionMode $mode): BuilderEngineInterface;
}
