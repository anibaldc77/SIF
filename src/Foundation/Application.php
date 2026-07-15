<?php

declare(strict_types=1);

namespace Sif\Foundation;

use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\EnvironmentInterface;
use Sif\Foundation\Contracts\KernelInterface;
use Sif\Foundation\Contracts\RuntimeInterface;
use Sif\Foundation\Exceptions\InvalidCapabilityException;

/** Owns the isolated runtime graph and its ordered provider collection. */
final class Application implements ApplicationInterface
{
    /** @var list<string> */
    private array $capabilities = [
        'runtime',
        'foundation',
        'providers',
        'lifecycle',
    ];

    public function __construct(
        private readonly RuntimeInterface $runtime,
        private readonly KernelInterface $kernel,
        private readonly EnvironmentInterface $environment,
        private readonly ServiceProviderCollection $providers,
    ) {
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
        return $this->capabilities;
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($this->normalizeCapability($capability), $this->capabilities, true);
    }

    public function addCapability(string $capability): void
    {
        $capability = $this->normalizeCapability($capability);

        if (!in_array($capability, $this->capabilities, true)) {
            $this->capabilities[] = $capability;
        }
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

        if (
            !preg_match('/^[a-z0-9_-]+(?:\.[a-z0-9_-]+)*$/D', $capability)
        ) {
            throw InvalidCapabilityException::invalid($capability);
        }

        return $capability;
    }
}
