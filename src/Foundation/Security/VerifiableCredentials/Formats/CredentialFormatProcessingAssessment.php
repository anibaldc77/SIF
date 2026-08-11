<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats;

final readonly class CredentialFormatProcessingAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private bool $issuerTrusted,
        private bool $signatureValid,
        private bool $claimsValid,
        private bool $holderBindingValid,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid
            && $this->issuerTrusted
            && $this->signatureValid
            && $this->claimsValid
            && $this->holderBindingValid;
    }

    public function issuerTrusted(): bool
    {
        return $this->issuerTrusted;
    }

    public function signatureValid(): bool
    {
        return $this->signatureValid;
    }

    public function claimsValid(): bool
    {
        return $this->claimsValid;
    }

    public function holderBindingValid(): bool
    {
        return $this->holderBindingValid;
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
