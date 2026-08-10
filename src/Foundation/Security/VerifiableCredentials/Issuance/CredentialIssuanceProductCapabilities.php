<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;
final readonly class CredentialIssuanceProductCapabilities
{
    public function __construct(
        private bool $credentialOffers = true,
        private bool $authorizationCodeGrant = true,
        private bool $preAuthorizedCodeGrant = true,
        private bool $proofOfPossession = true,
        private bool $batchIssuance = true,
        private bool $deferredIssuance = true,
        private bool $issuerMetadataDiscovery = true,
        private bool $transactionBinding = true,
        private bool $notifications = true,
        private bool $operationalReadiness = true
    ) {}
    public function credentialOffers(): bool { return $this->credentialOffers; }
    public function authorizationCodeGrant(): bool { return $this->authorizationCodeGrant; }
    public function preAuthorizedCodeGrant(): bool { return $this->preAuthorizedCodeGrant; }
    public function proofOfPossession(): bool { return $this->proofOfPossession; }
    public function batchIssuance(): bool { return $this->batchIssuance; }
    public function deferredIssuance(): bool { return $this->deferredIssuance; }
    public function issuerMetadataDiscovery(): bool { return $this->issuerMetadataDiscovery; }
    public function transactionBinding(): bool { return $this->transactionBinding; }
    public function notifications(): bool { return $this->notifications; }
    public function operationalReadiness(): bool { return $this->operationalReadiness; }
    /** @return array<string, bool> */
    public function toArray(): array
    {
        return [
            'credential_offers' => $this->credentialOffers,
            'authorization_code_grant' => $this->authorizationCodeGrant,
            'pre_authorized_code_grant' => $this->preAuthorizedCodeGrant,
            'proof_of_possession' => $this->proofOfPossession,
            'batch_issuance' => $this->batchIssuance,
            'deferred_issuance' => $this->deferredIssuance,
            'issuer_metadata_discovery' => $this->issuerMetadataDiscovery,
            'transaction_binding' => $this->transactionBinding,
            'notifications' => $this->notifications,
            'operational_readiness' => $this->operationalReadiness,
        ];
    }
}
