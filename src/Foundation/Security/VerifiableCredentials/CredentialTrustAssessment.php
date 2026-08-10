<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

final readonly class CredentialTrustAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $trusted,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function trusted(): bool
    {
        return $this->trusted;
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
