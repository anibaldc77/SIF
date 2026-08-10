<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

final readonly class CredentialAuthorizationAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $authorized,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function authorized(): bool
    {
        return $this->authorized;
    }

    /**
     * @return list<string>
     */
    public function violations(): array
    {
        return $this->violations;
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
