<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules;

use Sif\Foundation\Modules\Contracts\ModuleEnablementPolicyInterface;
use Sif\Foundation\Modules\Contracts\ModuleInterface;
use Sif\Foundation\Modules\Contracts\ModulePlanResolverInterface;
use Sif\Foundation\Modules\Contracts\MutableModuleRegistryInterface;
use Sif\Foundation\Modules\Exceptions\DisabledRequiredModuleException;
use Sif\Foundation\Modules\Planning\DisabledModule;
use Sif\Foundation\Modules\Planning\ResolvedModulePlan;

final readonly class ModulePlanResolver implements ModulePlanResolverInterface
{
    public function __construct(
        private ModuleDependencyResolver $dependencyResolver = new ModuleDependencyResolver(),
    ) {
    }

    public function resolve(
        MutableModuleRegistryInterface $registry,
        ModuleEnablementPolicyInterface $policy,
    ): ResolvedModulePlan {
        $decisions = [];
        $enabledRegistry = new ModuleRegistry();
        $disabled = [];

        foreach ($registry->descriptors() as $descriptor) {
            $decision = $policy->decide($descriptor);
            $decisions[$descriptor->id()->value()] = $decision;
            if ($decision->isEnabled()) {
                $enabledRegistry->register(new class ($descriptor) implements ModuleInterface {
                    public function __construct(private ModuleDescriptor $descriptor)
                    {
                    }

                    public function descriptor(): ModuleDescriptor
                    {
                        return $this->descriptor;
                    }
                });
                continue;
            }

            $disabled[] = new DisabledModule($descriptor, (string) $decision->reasonCode());
        }

        foreach ($enabledRegistry->descriptors() as $descriptor) {
            foreach ($descriptor->requiredDependencies() as $dependency) {
                $decision = $decisions[$dependency->moduleId()->value()] ?? null;
                if ($decision !== null && !$decision->isEnabled()) {
                    throw DisabledRequiredModuleException::forDependency(
                        $descriptor->id(),
                        $dependency->moduleId(),
                    );
                }
            }
        }

        $analysis = $this->dependencyResolver->analyze($enabledRegistry);
        $registry->freeze();

        return new ResolvedModulePlan(
            $analysis->orderedDescriptors(),
            $disabled,
            $analysis->dependenciesByModule(),
        );
    }
}
