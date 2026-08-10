<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

final readonly class CredentialStatusAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $acceptable,
        private CredentialStatusEvidence $evidence,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function acceptable(): bool
    {
        return $this->acceptable;
    }

    public function evidence(): CredentialStatusEvidence
    {
        return $this->evidence;
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
