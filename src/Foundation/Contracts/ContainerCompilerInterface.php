<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Container\CompiledContainerDefinition;

interface ContainerCompilerInterface
{
    public function compile(): CompiledContainerDefinition;
}
