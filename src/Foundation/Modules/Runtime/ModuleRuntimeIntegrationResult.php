<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Runtime;

use Sif\Foundation\Modules\Composition\ComposedModuleContributions;
use Sif\Foundation\Modules\Diagnostics\ModuleRuntimeDiagnostic;
use Sif\Foundation\Modules\Planning\ResolvedModulePlan;

final readonly class ModuleRuntimeIntegrationResult
{
    /** @param list<ModuleRuntimeDiagnostic> $diagnostics */
    public function __construct(
        private ResolvedModulePlan $plan,
        private ComposedModuleContributions $contributions,
        private string $fingerprint,
        private array $diagnostics,
    ) {
    }

    public function plan(): ResolvedModulePlan
    {
        return $this->plan;
    }

    public function contributions(): ComposedModuleContributions
    {
        return $this->contributions;
    }

    public function fingerprint(): string
    {
        return $this->fingerprint;
    }

    /** @return list<ModuleRuntimeDiagnostic> */
    public function diagnostics(): array
    {
        return $this->diagnostics;
    }
}
