<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust;

final readonly class CredentialTrustChainAssessment
{
    /**
     * @param list<string> $pathEntityIds
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $trusted,
        private array $pathEntityIds = [],
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function trusted(): bool
    {
        return $this->trusted;
    }

    /** @return list<string> */
    public function pathEntityIds(): array
    {
        return $this->pathEntityIds;
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
