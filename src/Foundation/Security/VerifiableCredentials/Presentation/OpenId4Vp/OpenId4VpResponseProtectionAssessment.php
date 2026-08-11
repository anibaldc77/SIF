<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

final readonly class OpenId4VpResponseProtectionAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private bool $verifierBound,
        private bool $nonceValid,
        private bool $stateValid,
        private bool $transactionBound,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid
            && $this->verifierBound
            && $this->nonceValid
            && $this->stateValid
            && $this->transactionBound;
    }

    public function verifierBound(): bool
    {
        return $this->verifierBound;
    }

    public function nonceValid(): bool
    {
        return $this->nonceValid;
    }

    public function stateValid(): bool
    {
        return $this->stateValid;
    }

    public function transactionBound(): bool
    {
        return $this->transactionBound;
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
