<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration;

final readonly class ConfigurationLoadResult
{
    /** @param list<ConfigurationDiagnostic> $diagnostics */
    public function __construct(
        public ?RepositoryConfiguration $configuration,
        public array $diagnostics = [],
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->configuration !== null && $this->diagnostics === [];
    }
}
