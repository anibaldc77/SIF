<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats;

final readonly class HighAssuranceCredentialProductCapabilities
{
    public function __construct(
        private bool $sdJwtVc = true,
        private bool $isoMdoc = true,
        private bool $selectiveDisclosure = true,
        private bool $issuerTrust = true,
        private bool $statusValidation = true,
        private bool $holderBinding = true,
        private bool $msoValidation = true,
        private bool $deviceAuthentication = true,
        private bool $openid4VciInteroperability = true,
        private bool $openid4VpInteroperability = true,
        private bool $privacyPolicy = true,
        private bool $operationalReadiness = true
    ) {
    }

    public function sdJwtVc(): bool { return $this->sdJwtVc; }
    public function isoMdoc(): bool { return $this->isoMdoc; }
    public function selectiveDisclosure(): bool { return $this->selectiveDisclosure; }
    public function issuerTrust(): bool { return $this->issuerTrust; }
    public function statusValidation(): bool { return $this->statusValidation; }
    public function holderBinding(): bool { return $this->holderBinding; }
    public function msoValidation(): bool { return $this->msoValidation; }
    public function deviceAuthentication(): bool { return $this->deviceAuthentication; }
    public function openid4VciInteroperability(): bool { return $this->openid4VciInteroperability; }
    public function openid4VpInteroperability(): bool { return $this->openid4VpInteroperability; }
    public function privacyPolicy(): bool { return $this->privacyPolicy; }
    public function operationalReadiness(): bool { return $this->operationalReadiness; }

    /** @return array<string, bool> */
    public function toArray(): array
    {
        return [
            'sd_jwt_vc' => $this->sdJwtVc,
            'iso_mdoc' => $this->isoMdoc,
            'selective_disclosure' => $this->selectiveDisclosure,
            'issuer_trust' => $this->issuerTrust,
            'status_validation' => $this->statusValidation,
            'holder_binding' => $this->holderBinding,
            'mso_validation' => $this->msoValidation,
            'device_authentication' => $this->deviceAuthentication,
            'openid4vci_interoperability' => $this->openid4VciInteroperability,
            'openid4vp_interoperability' => $this->openid4VpInteroperability,
            'privacy_policy' => $this->privacyPolicy,
            'operational_readiness' => $this->operationalReadiness,
        ];
    }
}
