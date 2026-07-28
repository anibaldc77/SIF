<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Contracts;

use Sif\Foundation\Container\ServiceDefinition;

interface ModuleContainerContributionInterface
{
    /** @return list<ServiceDefinition> */
    public function serviceDefinitions(): array;
}
