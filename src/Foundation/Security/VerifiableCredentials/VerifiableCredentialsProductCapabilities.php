<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

final readonly class VerifiableCredentialsProductCapabilities
{
    public function __construct(
        private bool $presentationBinding = true,
        private bool $credentialFormatExtensibility = true,
        private bool $trustValidation = true,
        private bool $selectiveDisclosure = true,
        private bool $holderBinding = true,
        private bool $identityAssurance = true,
        private bool $credentialStatus = true,
        private bool $operationalReadiness = true
    ) {
    }

    public function presentationBinding(): bool
    {
        return $this->presentationBinding;
    }

    public function credentialFormatExtensibility(): bool
    {
        return $this->credentialFormatExtensibility;
    }

    public function trustValidation(): bool
    {
        return $this->trustValidation;
    }

    public function selectiveDisclosure(): bool
    {
        return $this->selectiveDisclosure;
    }

    public function holderBinding(): bool
    {
        return $this->holderBinding;
    }

    public function identityAssurance(): bool
    {
        return $this->identityAssurance;
    }

    public function credentialStatus(): bool
    {
        return $this->credentialStatus;
    }

    public function operationalReadiness(): bool
    {
        return $this->operationalReadiness;
    }

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'presentation_binding' => $this->presentationBinding,
            'credential_format_extensibility' => $this->credentialFormatExtensibility,
            'trust_validation' => $this->trustValidation,
            'selective_disclosure' => $this->selectiveDisclosure,
            'holder_binding' => $this->holderBinding,
            'identity_assurance' => $this->identityAssurance,
            'credential_status' => $this->credentialStatus,
            'operational_readiness' => $this->operationalReadiness,
        ];
    }
}
