<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Contracts;

use Sif\Foundation\Modules\Planning\ResolvedModulePlan;

interface ModulePlanResolverInterface
{
    public function resolve(
        MutableModuleRegistryInterface $registry,
        ModuleEnablementPolicyInterface $policy,
    ): ResolvedModulePlan;
}
