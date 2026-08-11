<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats;

final readonly class HighAssurancePrivacyDecision
{
    /**
     * @param list<string> $disclosableClaims
     * @param list<string> $blockedClaims
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $allowed,
        private array $disclosableClaims = [],
        private array $blockedClaims = [],
        private array $warnings = []
    ) {
    }

    public function allowed(): bool
    {
        return $this->allowed;
    }

    /** @return list<string> */
    public function disclosableClaims(): array
    {
        return $this->disclosableClaims;
    }

    /** @return list<string> */
    public function blockedClaims(): array
    {
        return $this->blockedClaims;
    }

    /** @return list<string> */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
