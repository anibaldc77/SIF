<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Composition;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Configuration\Source\Contracts\ConfigurationSourceInterface;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Contracts\ServiceProviderInterface;

final readonly class ComposedModuleContributions
{
    /**
     * @param list<ConfigurationSourceInterface> $configurationSources
     * @param list<ServiceDefinition> $serviceDefinitions
     * @param list<CapabilityInterface> $capabilities
     * @param list<class-string<ServiceProviderInterface>> $serviceProviders
     * @param array<string, non-empty-string> $configurationNamespaceOwners
     * @param array<string, non-empty-string> $serviceDefinitionOwners
     * @param array<string, non-empty-string> $capabilityOwners
     * @param array<class-string<ServiceProviderInterface>, non-empty-string> $serviceProviderOwners
     */
    public function __construct(
        private array $configurationSources,
        private array $serviceDefinitions,
        private array $capabilities,
        private array $serviceProviders,
        private array $configurationNamespaceOwners,
        private array $serviceDefinitionOwners,
        private array $capabilityOwners,
        private array $serviceProviderOwners,
    ) {
    }

    /** @return list<ConfigurationSourceInterface> */
    public function configurationSources(): array { return $this->configurationSources; }
    /** @return list<ServiceDefinition> */
    public function serviceDefinitions(): array { return $this->serviceDefinitions; }
    /** @return list<CapabilityInterface> */
    public function capabilities(): array { return $this->capabilities; }
    /** @return list<class-string<ServiceProviderInterface>> */
    public function serviceProviders(): array { return $this->serviceProviders; }
    /** @return array<string, non-empty-string> */
    public function configurationNamespaceOwners(): array { return $this->configurationNamespaceOwners; }
    /** @return array<string, non-empty-string> */
    public function serviceDefinitionOwners(): array { return $this->serviceDefinitionOwners; }
    /** @return array<string, non-empty-string> */
    public function capabilityOwners(): array { return $this->capabilityOwners; }
    /** @return array<class-string<ServiceProviderInterface>, non-empty-string> */
    public function serviceProviderOwners(): array { return $this->serviceProviderOwners; }
}
