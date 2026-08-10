<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

final readonly class FapiDeploymentConformanceAssessment
{
    /**
     * @param list<string> $missingCapabilities
     * @param list<string> $missingSecurityControls
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $conformant,
        private array $missingCapabilities = [],
        private array $missingSecurityControls = [],
        private array $warnings = []
    ) {
    }

    public function conformant(): bool
    {
        return $this->conformant;
    }

    /** @return list<string> */
    public function missingCapabilities(): array
    {
        return $this->missingCapabilities;
    }

    /** @return list<string> */
    public function missingSecurityControls(): array
    {
        return $this->missingSecurityControls;
    }

    /** @return list<string> */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
