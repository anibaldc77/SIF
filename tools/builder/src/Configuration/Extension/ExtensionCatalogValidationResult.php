<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Extension;

use Sif\Builder\Configuration\ConfigurationDiagnostic;
use Sif\Builder\Configuration\Profile\ResolvedBuildProfile;

final readonly class ExtensionCatalogValidationResult
{
    /** @param list<ConfigurationDiagnostic> $diagnostics */
    public function __construct(
        public ?ResolvedBuildProfile $profile,
        public array $diagnostics = [],
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->profile !== null && $this->diagnostics === [];
    }
}
