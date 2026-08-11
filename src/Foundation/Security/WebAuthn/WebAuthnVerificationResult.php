<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnVerificationResult
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private bool $challengeValid,
        private bool $originValid,
        private bool $relyingPartyValid,
        private bool $userVerificationSatisfied,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid
            && $this->challengeValid
            && $this->originValid
            && $this->relyingPartyValid
            && $this->userVerificationSatisfied;
    }

    public function challengeValid(): bool
    {
        return $this->challengeValid;
    }

    public function originValid(): bool
    {
        return $this->originValid;
    }

    public function relyingPartyValid(): bool
    {
        return $this->relyingPartyValid;
    }

    public function userVerificationSatisfied(): bool
    {
        return $this->userVerificationSatisfied;
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
