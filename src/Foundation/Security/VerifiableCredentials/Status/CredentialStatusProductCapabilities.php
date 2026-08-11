<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status;

final readonly class CredentialStatusProductCapabilities
{
    public function statusResolution(): bool
    {
        return true;
    }

    public function revocationAndSuspension(): bool
    {
        return true;
    }

    public function bitstringStatusList(): bool
    {
        return true;
    }

    public function tokenStatusList(): bool
    {
        return true;
    }

    public function issuerPublicationLifecycle(): bool
    {
        return true;
    }

    public function verifierCaching(): bool
    {
        return true;
    }

    public function freshnessAndRefresh(): bool
    {
        return true;
    }

    public function resolutionFailurePolicy(): bool
    {
        return true;
    }

    public function highAssuranceEnforcement(): bool
    {
        return true;
    }

    public function operationalReadiness(): bool
    {
        return true;
    }

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'status_resolution' => $this->statusResolution(),
            'revocation_and_suspension' => $this->revocationAndSuspension(),
            'bitstring_status_list' => $this->bitstringStatusList(),
            'token_status_list' => $this->tokenStatusList(),
            'issuer_publication_lifecycle' => $this->issuerPublicationLifecycle(),
            'verifier_caching' => $this->verifierCaching(),
            'freshness_and_refresh' => $this->freshnessAndRefresh(),
            'resolution_failure_policy' => $this->resolutionFailurePolicy(),
            'high_assurance_enforcement' => $this->highAssuranceEnforcement(),
            'operational_readiness' => $this->operationalReadiness(),
        ];
    }
}