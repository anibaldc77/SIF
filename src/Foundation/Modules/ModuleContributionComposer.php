<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules;

use Sif\Foundation\Contracts\ServiceProviderInterface;
use Sif\Foundation\Modules\Composition\ComposedModuleContributions;
use Sif\Foundation\Modules\Contracts\ModuleContributionComposerInterface;
use Sif\Foundation\Modules\Contracts\ModuleContributionProviderInterface;
use Sif\Foundation\Modules\Contracts\ModuleRegistryInterface;
use Sif\Foundation\Modules\Exceptions\InvalidModuleCompositionException;
use Sif\Foundation\Modules\Exceptions\ModuleContributionCollisionException;
use Sif\Foundation\Modules\Planning\ResolvedModulePlan;

final readonly class ModuleContributionComposer implements ModuleContributionComposerInterface
{
    public function compose(ResolvedModulePlan $plan, ModuleRegistryInterface $registry): ComposedModuleContributions
    {
        $sources = [];
        $definitions = [];
        $capabilities = [];
        $providers = [];
        $namespaceOwners = [];
        $definitionOwners = [];
        $capabilityOwners = [];
        $providerOwners = [];

        foreach ($plan->enabledModules() as $descriptor) {
            $moduleId = $descriptor->id()->value();
            $module = $registry->module($descriptor->id());
            if ($module === null) {
                throw InvalidModuleCompositionException::missingModule($moduleId);
            }

            $set = $module instanceof ModuleContributionProviderInterface
                ? $module->contributions()
                : new Contribution\ModuleContributionSet();

            $namespace = $set->configurationNamespace()?->value();
            if ($namespace !== $descriptor->configurationNamespace()) {
                if ($namespace !== null || $descriptor->configurationNamespace() !== null) {
                    throw InvalidModuleCompositionException::namespaceMismatch($moduleId);
                }
            }
            if ($namespace !== null) {
                $this->claim('configuration namespace', $namespace, $moduleId, $namespaceOwners);
            }

            foreach ($set->configurationSources() as $source) {
                $sources[] = $source;
            }
            foreach ($set->serviceDefinitions() as $definition) {
                $id = $definition->identifier()->value();
                $this->claim('service definition', $id, $moduleId, $definitionOwners);
                $definitions[] = $definition;
            }

            $contributedCapabilityIds = [];
            foreach ($set->capabilities() as $capability) {
                $id = $capability->identifier();
                if (!in_array($id, $descriptor->providedCapabilities(), true)) {
                    throw InvalidModuleCompositionException::undeclaredCapability($moduleId, $id);
                }
                $this->claim('capability', $id, $moduleId, $capabilityOwners);
                $contributedCapabilityIds[] = $id;
                $capabilities[] = $capability;
            }
            foreach ($descriptor->providedCapabilities() as $id) {
                if (!in_array($id, $contributedCapabilityIds, true)) {
                    throw InvalidModuleCompositionException::missingCapability($moduleId, $id);
                }
            }

            foreach ($descriptor->serviceProviders() as $provider) {
                if (!is_a($provider, ServiceProviderInterface::class, true)) {
                    throw InvalidModuleCompositionException::invalidServiceProvider($moduleId, $provider);
                }
                /** @var class-string<ServiceProviderInterface> $provider */
                $this->claim('service provider', $provider, $moduleId, $providerOwners);
                $providers[] = $provider;
            }
        }

        return new ComposedModuleContributions(
            $sources,
            $definitions,
            $capabilities,
            $providers,
            $namespaceOwners,
            $definitionOwners,
            $capabilityOwners,
            $providerOwners,
        );
    }

    /** @param array<string, non-empty-string> $owners */
    private function claim(string $type, string $identifier, string $moduleId, array &$owners): void
    {
        if (isset($owners[$identifier])) {
            throw ModuleContributionCollisionException::forContribution($type, $identifier, $owners[$identifier], $moduleId);
        }
        $owners[$identifier] = $moduleId;
    }
}
