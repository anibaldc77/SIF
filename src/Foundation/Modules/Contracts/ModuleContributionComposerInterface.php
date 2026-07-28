<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Contracts;

use Sif\Foundation\Modules\Composition\ComposedModuleContributions;
use Sif\Foundation\Modules\Planning\ResolvedModulePlan;

interface ModuleContributionComposerInterface
{
    public function compose(
        ResolvedModulePlan $plan,
        ModuleRegistryInterface $registry,
    ): ComposedModuleContributions;
}
