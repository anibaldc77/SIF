<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Profile;

use Sif\Builder\Configuration\ConfigurationDiagnostic;

final readonly class BuildProfileResolutionResult
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
