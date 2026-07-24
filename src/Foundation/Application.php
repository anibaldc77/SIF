<?php

declare(strict_types=1);

namespace Sif\Foundation;

use Sif\Foundation\Capability\CapabilityRegistry;
use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Configuration\ConfigurationRepository;
use Sif\Foundation\Configuration\Contracts\MutableConfigurationInterface;
use Sif\Foundation\Contracts\EnvironmentAwareApplicationInterface;
use Sif\Foundation\Contracts\EnvironmentInterface;
use Sif\Foundation\Environment\Contracts\MutableEnvironmentInterface;
use Sif\Foundation\Environment\EnvironmentRepository;
use Sif\Foundation\Contracts\KernelInterface;
use Sif\Foundation\Contracts\RuntimeInterface;
use Sif\Foundation\Exceptions\InvalidCapabilityException;

/** Owns the isolated runtime graph and its ordered provider collection. */
final class Application implements EnvironmentAwareApplicationInterface
{
    private CapabilityRegistry $capabilityRegistry;

    private MutableConfigurationInterface $configuration;

    private MutableEnvironmentInterface $variables;

    public function __construct(
        private readonly RuntimeInterface $runtime,
        private readonly KernelInterface $kernel,
        private readonly EnvironmentInterface $environment,
        private readonly ServiceProviderCollection $providers,
        ?CapabilityRegistry $capabilityRegistry = null,
        ?MutableConfigurationInterface $configuration = null,
        ?MutableEnvironmentInterface $variables = null,
    ) {
        $this->configuration = $configuration ?? new ConfigurationRepository();
        $this->variables = $variables ?? new EnvironmentRepository();
        $this->capabilityRegistry = $capabilityRegistry ?? new CapabilityRegistry();

        foreach (['runtime', 'foundation', 'providers', 'lifecycle', 'configuration'] as $identifier) {
            if (!$this->capabilityRegistry->has($identifier)) {
                $this->capabilityRegistry->register(new NamedCapability($identifier));
            }
        }
    }

    public function runtime(): RuntimeInterface
    {
        return $this->runtime;
    }

    public function kernel(): KernelInterface
    {
        return $this->kernel;
    }

    public function environment(): EnvironmentInterface
    {
        return $this->environment;
    }

    public function providers(): ServiceProviderCollection
    {
        return $this->providers;
    }

    /** @return list<string> */
    public function capabilities(): array
    {
        return array_map(
            static fn (CapabilityInterface $capability): string => $capability->identifier(),
            $this->capabilityRegistry->all(),
        );
    }

    public function hasCapability(string $capability): bool
    {
        return $this->capabilityRegistry->has($this->normalizeCapability($capability));
    }

    public function addCapability(string $capability): void
    {
        $identifier = $this->normalizeCapability($capability);

        if (!$this->capabilityRegistry->has($identifier)) {
            $this->capabilityRegistry->register(new NamedCapability($identifier));
        }
    }

    public function capabilityRegistry(): CapabilityRegistry
    {
        return $this->capabilityRegistry;
    }

    public function configuration(): MutableConfigurationInterface
    {
        return $this->configuration;
    }

    public function variables(): MutableEnvironmentInterface
    {
        return $this->variables;
    }

    public function registerCapability(CapabilityInterface $capability): void
    {
        $identifier = $this->normalizeCapability($capability->identifier());

        if ($identifier !== $capability->identifier()) {
            throw InvalidCapabilityException::invalid($capability->identifier());
        }

        $this->capabilityRegistry->register($capability);
    }

    public function capability(string $identifier): CapabilityInterface
    {
        return $this->capabilityRegistry->get($this->normalizeCapability($identifier));
    }

    public function boot(): BootResult
    {
        return $this->kernel->boot($this);
    }

    public function run(): BootResult
    {
        return $this->kernel->run($this);
    }

    public function shutdown(): BootResult
    {
        return $this->kernel->shutdown($this);
    }

    private function normalizeCapability(string $capability): string
    {
        $capability = strtolower(trim($capability));

        if ($capability === '') {
            throw InvalidCapabilityException::empty();
        }

        if (str_contains($capability, ' ')) {
            throw InvalidCapabilityException::invalid($capability);
        }

        if (!preg_match('/^[a-z0-9_-]+(?:\.[a-z0-9_-]+)*$/D', $capability)) {
            throw InvalidCapabilityException::invalid($capability);
        }

        return $capability;
    }
}
