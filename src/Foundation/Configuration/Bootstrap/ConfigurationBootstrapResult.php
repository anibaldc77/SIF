<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Bootstrap;

use Sif\Foundation\Configuration\Composition\ConfigurationProvenance;
use Sif\Foundation\Configuration\Diagnostics\ConfigurationDiagnostic;
use Sif\Foundation\Configuration\Snapshot\ConfigurationSnapshot;

final readonly class ConfigurationBootstrapResult
{
    /**
     * @param array<string, ConfigurationProvenance> $provenance
     * @param list<ConfigurationDiagnostic> $diagnostics
     */
    public function __construct(
        private ConfigurationSnapshot $snapshot,
        private bool $cacheHit,
        private array $provenance = [],
        private array $diagnostics = [],
    ) {
    }

    public function snapshot(): ConfigurationSnapshot
    {
        return $this->snapshot;
    }

    public function cacheHit(): bool
    {
        return $this->cacheHit;
    }

    /** @return array<string, ConfigurationProvenance> */
    public function provenance(): array
    {
        return $this->provenance;
    }

    /** @return list<ConfigurationDiagnostic> */
    public function diagnostics(): array
    {
        return $this->diagnostics;
    }
}
