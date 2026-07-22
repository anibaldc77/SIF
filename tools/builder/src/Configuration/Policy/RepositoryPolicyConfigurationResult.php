<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Policy;

use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicySet;
use Sif\Builder\Configuration\ConfigurationDiagnostic;

final readonly class RepositoryPolicyConfigurationResult
{
    /** @param list<ConfigurationDiagnostic> $diagnostics */
    public function __construct(
        public ?RepositoryPolicySet $policies,
        public array $diagnostics = [],
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->policies !== null && $this->diagnostics === [];
    }
}
