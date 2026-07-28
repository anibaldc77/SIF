<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Contracts;

use Sif\Foundation\Modules\Resolution\DependencyGraphAnalysis;

interface ModuleDependencyResolverInterface
{
    public function analyze(ModuleRegistryInterface $registry): DependencyGraphAnalysis;
}
