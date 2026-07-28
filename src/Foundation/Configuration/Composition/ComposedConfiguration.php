<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Composition;

use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\Diagnostics\ConfigurationDiagnostic;
use Sif\Foundation\Configuration\ImmutableConfigurationRepository;

final readonly class ComposedConfiguration
{
    /**
     * @param array<string, ConfigurationProvenance> $provenance
     * @param list<ConfigurationDiagnostic> $diagnostics
     */
    public function __construct(
        private ImmutableConfigurationRepository $repository,
        private array $provenance,
        private array $diagnostics = [],
    ) {
    }

    public function repository(): ImmutableConfigurationRepository
    {
        return $this->repository;
    }

    public function provenance(string|ConfigurationKey $key): ?ConfigurationProvenance
    {
        $normalized = $key instanceof ConfigurationKey
            ? $key->value()
            : (new ConfigurationKey($key))->value();

        return $this->provenance[$normalized] ?? null;
    }

    /** @return array<string, ConfigurationProvenance> */
    public function allProvenance(): array
    {
        return $this->provenance;
    }

    /** @return list<ConfigurationDiagnostic> */
    public function diagnostics(): array
    {
        return $this->diagnostics;
    }
}
