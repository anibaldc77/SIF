<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnAssertionValidationResult
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
        private bool $signatureValid,
        private bool $userVerificationSatisfied,
        private bool $counterValid,
        private ?int $newSignatureCounter = null,
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
            && $this->signatureValid
            && $this->userVerificationSatisfied
            && $this->counterValid;
    }

    public function challengeValid(): bool { return $this->challengeValid; }
    public function originValid(): bool { return $this->originValid; }
    public function relyingPartyValid(): bool { return $this->relyingPartyValid; }
    public function signatureValid(): bool { return $this->signatureValid; }
    public function userVerificationSatisfied(): bool { return $this->userVerificationSatisfied; }
    public function counterValid(): bool { return $this->counterValid; }
    public function newSignatureCounter(): ?int { return $this->newSignatureCounter; }

    /** @return list<string> */
    public function violations(): array { return $this->violations; }

    /** @return list<string> */
    public function warnings(): array { return $this->warnings; }
}
