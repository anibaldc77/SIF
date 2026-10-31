<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

final readonly class CredentialProofValidationResult
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private bool $nonceValid,
        private bool $holderBound,
        private bool $replaySafe,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid
            && $this->nonceValid
            && $this->holderBound
            && $this->replaySafe;
    }

    /**
     * Generic proof verification and freshness, without an independent
     * private-key possession requirement. Only mechanism-specific policies
     * may use this to accept an attestation-based request.
     */
    public function validWithoutHolderBinding(): bool
    {
        return $this->valid && $this->nonceValid && $this->replaySafe;
    }
    public function nonceValid(): bool
    {
        return $this->nonceValid;
    }

    public function holderBound(): bool
    {
        return $this->holderBound;
    }

    public function replaySafe(): bool
    {
        return $this->replaySafe;
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
