<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnAttestationTrustContext
{
    /**
     * @param list<string> $allowedAttestationFormats
     * @param list<string> $requiredCertifications
     */
    public function __construct(
        private bool $attestationRequired = false,
        private array $allowedAttestationFormats = [],
        private array $requiredCertifications = [],
        private bool $metadataRequired = false
    ) {
    }

    public function attestationRequired(): bool
    {
        return $this->attestationRequired;
    }

    /**
     * @return list<string>
     */
    public function allowedAttestationFormats(): array
    {
        return $this->allowedAttestationFormats;
    }

    /**
     * @return list<string>
     */
    public function requiredCertifications(): array
    {
        return $this->requiredCertifications;
    }

    public function metadataRequired(): bool
    {
        return $this->metadataRequired;
    }
}
