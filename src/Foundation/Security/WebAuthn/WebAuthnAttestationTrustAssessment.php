<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnAttestationTrustAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $trusted,
        private bool $attestationValid,
        private bool $metadataTrusted,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function trusted(): bool
    {
        return $this->trusted
            && $this->attestationValid
            && $this->metadataTrusted;
    }

    public function attestationValid(): bool
    {
        return $this->attestationValid;
    }

    public function metadataTrusted(): bool
    {
        return $this->metadataTrusted;
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
