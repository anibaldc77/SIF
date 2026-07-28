<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Runtime;

use ReflectionClass;
use Sif\Foundation\Capability\CapabilityRegistry;
use Sif\Foundation\Configuration\Composition\ConfigurationComposer;
use Sif\Foundation\Configuration\Contracts\MutableConfigurationInterface;
use Sif\Foundation\Configuration\Loader\ConfigurationMerger;
use Sif\Foundation\Container\ServiceDefinitionRegistry;
use Sif\Foundation\Contracts\ServiceProviderInterface;
use Sif\Foundation\Modules\Contracts\ModuleContributionComposerInterface;
use Sif\Foundation\Modules\Contracts\ModuleEnablementPolicyInterface;
use Sif\Foundation\Modules\Contracts\ModulePlanResolverInterface;
use Sif\Foundation\Modules\Contracts\MutableModuleRegistryInterface;
use Sif\Foundation\Modules\Diagnostics\ModuleRuntimeDiagnostic;
use Sif\Foundation\Modules\Exceptions\ModuleRuntimeIntegrationException;
use Sif\Foundation\Modules\ModuleContributionComposer;
use Sif\Foundation\Modules\ModulePlanResolver;
use Sif\Foundation\ServiceProviderCollection;
use Throwable;

final readonly class ModuleRuntimeBootstrapper
{
    public function __construct(
        private MutableModuleRegistryInterface $registry,
        private ModuleEnablementPolicyInterface $policy,
        private ModulePlanResolverInterface $planResolver = new ModulePlanResolver(),
        private ModuleContributionComposerInterface $contributionComposer = new ModuleContributionComposer(),
        private ConfigurationComposer $configurationComposer = new ConfigurationComposer(),
        private ConfigurationMerger $configurationMerger = new ConfigurationMerger(),
    ) {
    }

    public function integrate(
        MutableConfigurationInterface $configuration,
        ServiceDefinitionRegistry $serviceDefinitions,
        CapabilityRegistry $capabilities,
        ServiceProviderCollection $providers,
    ): ModuleRuntimeIntegrationResult {
        $plan = $this->planResolver->resolve($this->registry, $this->policy);
        $contributions = $this->contributionComposer->compose($plan, $this->registry);

        $this->validateRequiredCapabilities($plan, $capabilities, $contributions->capabilities());

        if ($contributions->configurationSources() !== []) {
            $composed = $this->configurationComposer->compose($contributions->configurationSources());
            $configuration->replace($this->configurationMerger->merge(
                $configuration->all(),
                $composed->repository()->all(),
            ));
        }

        foreach ($contributions->serviceDefinitions() as $definition) {
            $serviceDefinitions->register($definition);
        }
        foreach ($contributions->capabilities() as $capability) {
            $capabilities->register($capability);
        }
        foreach ($contributions->serviceProviders() as $providerClass) {
            $providers->add($this->instantiateProvider($providerClass));
        }

        $fingerprint = $this->fingerprint($plan, $contributions);

        return new ModuleRuntimeIntegrationResult(
            $plan,
            $contributions,
            $fingerprint,
            [new ModuleRuntimeDiagnostic(
                'MODULE_RUNTIME_INTEGRATED',
                'The resolved module plan was integrated into the runtime graph.',
                [
                    'enabled_modules' => count($plan->enabledModules()),
                    'disabled_modules' => count($plan->disabledModules()),
                    'fingerprint' => $fingerprint,
                ],
            )],
        );
    }

    /**
     * @param list<\Sif\Foundation\Capability\Contracts\CapabilityInterface> $provided
     */
    private function validateRequiredCapabilities(
        \Sif\Foundation\Modules\Planning\ResolvedModulePlan $plan,
        CapabilityRegistry $capabilities,
        array $provided,
    ): void {
        $available = [];
        foreach ($capabilities->all() as $capability) {
            $available[$capability->identifier()] = true;
        }
        foreach ($provided as $capability) {
            $available[$capability->identifier()] = true;
        }

        foreach ($plan->enabledModules() as $descriptor) {
            foreach ($descriptor->requiredCapabilities() as $capability) {
                if (!isset($available[$capability])) {
                    throw ModuleRuntimeIntegrationException::missingCapability(
                        $descriptor->id()->value(),
                        $capability,
                    );
                }
            }
        }
    }

    /** @param class-string<ServiceProviderInterface> $providerClass */
    private function instantiateProvider(string $providerClass): ServiceProviderInterface
    {
        try {
            $reflection = new ReflectionClass($providerClass);
            $constructor = $reflection->getConstructor();
            if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
                throw new \RuntimeException('The provider constructor requires arguments.');
            }

            return $reflection->newInstance();
        } catch (Throwable $exception) {
            throw ModuleRuntimeIntegrationException::providerInstantiation($providerClass, $exception);
        }
    }

    private function fingerprint(
        \Sif\Foundation\Modules\Planning\ResolvedModulePlan $plan,
        \Sif\Foundation\Modules\Composition\ComposedModuleContributions $contributions,
    ): string {
        $payload = [
            'modules' => array_map(
                static fn ($descriptor): array => [
                    'id' => $descriptor->id()->value(),
                    'version' => $descriptor->version()->value(),
                ],
                $plan->enabledModules(),
            ),
            'disabled' => array_map(
                static fn ($disabled): array => [
                    'id' => $disabled->descriptor()->id()->value(),
                    'reason' => $disabled->reasonCode(),
                ],
                $plan->disabledModules(),
            ),
            'configuration_namespaces' => $contributions->configurationNamespaceOwners(),
            'services' => $contributions->serviceDefinitionOwners(),
            'capabilities' => $contributions->capabilityOwners(),
            'providers' => $contributions->serviceProviderOwners(),
        ];

        $encoded = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        return hash('sha256', $encoded);
    }
}
