<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

final readonly class IdentityAssuranceAssessment
{
    /**
     * @param list<string> $missingEvidence
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $satisfied,
        private array $missingEvidence = [],
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function satisfied(): bool
    {
        return $this->satisfied;
    }

    /** @return list<string> */
    public function missingEvidence(): array
    {
        return $this->missingEvidence;
    }

    /** @return list<string> */
    public function violations(): array
    {
        return $this->violations;
    }

    /** @return list<string> */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
