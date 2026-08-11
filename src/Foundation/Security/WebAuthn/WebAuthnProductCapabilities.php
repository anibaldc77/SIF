<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnProductCapabilities
{
    public function __construct(
        private bool $registration = true,
        private bool $authentication = true,
        private bool $attestationTrust = true,
        private bool $discoverableCredentials = true,
        private bool $passkeyUxPolicies = true,
        private bool $credentialLifecycle = true,
        private bool $recovery = true,
        private bool $deviceMigration = true,
        private bool $riskIntegration = true,
        private bool $operationalReadiness = true
    ) {
    }

    public function registration(): bool { return $this->registration; }
    public function authentication(): bool { return $this->authentication; }
    public function attestationTrust(): bool { return $this->attestationTrust; }
    public function discoverableCredentials(): bool { return $this->discoverableCredentials; }
    public function passkeyUxPolicies(): bool { return $this->passkeyUxPolicies; }
    public function credentialLifecycle(): bool { return $this->credentialLifecycle; }
    public function recovery(): bool { return $this->recovery; }
    public function deviceMigration(): bool { return $this->deviceMigration; }
    public function riskIntegration(): bool { return $this->riskIntegration; }
    public function operationalReadiness(): bool { return $this->operationalReadiness; }

    /** @return array<string, bool> */
    public function toArray(): array
    {
        return [
            'registration' => $this->registration,
            'authentication' => $this->authentication,
            'attestation_trust' => $this->attestationTrust,
            'discoverable_credentials' => $this->discoverableCredentials,
            'passkey_ux_policies' => $this->passkeyUxPolicies,
            'credential_lifecycle' => $this->credentialLifecycle,
            'recovery' => $this->recovery,
            'device_migration' => $this->deviceMigration,
            'risk_integration' => $this->riskIntegration,
            'operational_readiness' => $this->operationalReadiness,
        ];
    }
}
