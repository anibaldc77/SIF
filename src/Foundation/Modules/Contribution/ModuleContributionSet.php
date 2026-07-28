<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Contribution;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Configuration\Source\Contracts\ConfigurationSourceInterface;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Modules\Exceptions\InvalidModuleContributionException;

final readonly class ModuleContributionSet
{
    /**
     * @param list<ConfigurationSourceInterface> $configurationSources
     * @param list<ServiceDefinition> $serviceDefinitions
     * @param list<CapabilityInterface> $capabilities
     */
    public function __construct(
        private ?ModuleConfigurationNamespace $configurationNamespace = null,
        private array $configurationSources = [],
        private array $serviceDefinitions = [],
        private array $capabilities = [],
    ) {
        if ($configurationSources !== [] && $configurationNamespace === null) {
            throw InvalidModuleContributionException::missingConfigurationNamespace();
        }

        $this->assertUniqueConfigurationSources();
        $this->assertUniqueServiceDefinitions();
        $this->assertUniqueCapabilities();
    }

    public function configurationNamespace(): ?ModuleConfigurationNamespace
    {
        return $this->configurationNamespace;
    }

    /** @return list<ConfigurationSourceInterface> */
    public function configurationSources(): array
    {
        return $this->configurationSources;
    }

    /** @return list<ServiceDefinition> */
    public function serviceDefinitions(): array
    {
        return $this->serviceDefinitions;
    }

    /** @return list<CapabilityInterface> */
    public function capabilities(): array
    {
        return $this->capabilities;
    }

    private function assertUniqueConfigurationSources(): void
    {
        $seen = [];
        foreach ($this->configurationSources as $source) {
            $id = $source->id();
            if ($id === '' || isset($seen[$id])) {
                throw InvalidModuleContributionException::duplicateConfigurationSource($id);
            }
            $seen[$id] = true;
        }
    }

    private function assertUniqueServiceDefinitions(): void
    {
        $seen = [];
        foreach ($this->serviceDefinitions as $definition) {
            $id = $definition->identifier()->value();
            if (isset($seen[$id])) {
                throw InvalidModuleContributionException::duplicateServiceDefinition($id);
            }
            $seen[$id] = true;
        }
    }

    private function assertUniqueCapabilities(): void
    {
        $seen = [];
        foreach ($this->capabilities as $capability) {
            $id = $capability->identifier();
            if ($id === '' || isset($seen[$id])) {
                throw InvalidModuleContributionException::duplicateCapability($id);
            }
            $seen[$id] = true;
        }
    }
}
