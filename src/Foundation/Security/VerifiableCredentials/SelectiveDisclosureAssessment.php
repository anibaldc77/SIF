<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

final readonly class SelectiveDisclosureAssessment
{
    /**
     * @param list<string> $missingClaims
     * @param list<string> $excessClaims
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private array $missingClaims = [],
        private array $excessClaims = [],
        private array $warnings = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid;
    }

    /** @return list<string> */
    public function missingClaims(): array
    {
        return $this->missingClaims;
    }

    /** @return list<string> */
    public function excessClaims(): array
    {
        return $this->excessClaims;
    }

    /** @return list<string> */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
