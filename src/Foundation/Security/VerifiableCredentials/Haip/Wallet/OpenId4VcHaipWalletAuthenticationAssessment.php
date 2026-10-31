<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Wallet;

final readonly class OpenId4VcHaipWalletAuthenticationAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $conformant,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function conformant(): bool
    {
        return $this->conformant && $this->violations === [];
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
