<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

final readonly class FapiProductCapabilities
{
    public function __construct(
        private bool $clientProfile = true,
        private bool $authorizationServerProfile = true,
        private bool $parPkceIssuerMetadataConformance = true,
        private bool $senderConstrainedTokens = true,
        private bool $resourceServerEnforcement = true,
        private bool $messageSigning = true,
        private bool $deploymentConformance = true
    ) {
    }

    public function clientProfile(): bool { return $this->clientProfile; }
    public function authorizationServerProfile(): bool { return $this->authorizationServerProfile; }
    public function parPkceIssuerMetadataConformance(): bool { return $this->parPkceIssuerMetadataConformance; }
    public function senderConstrainedTokens(): bool { return $this->senderConstrainedTokens; }
    public function resourceServerEnforcement(): bool { return $this->resourceServerEnforcement; }
    public function messageSigning(): bool { return $this->messageSigning; }
    public function deploymentConformance(): bool { return $this->deploymentConformance; }

    /** @return array<string, bool> */
    public function toArray(): array
    {
        return [
            'client_profile' => $this->clientProfile,
            'authorization_server_profile' => $this->authorizationServerProfile,
            'par_pkce_issuer_metadata_conformance' => $this->parPkceIssuerMetadataConformance,
            'sender_constrained_tokens' => $this->senderConstrainedTokens,
            'resource_server_enforcement' => $this->resourceServerEnforcement,
            'message_signing' => $this->messageSigning,
            'deployment_conformance' => $this->deploymentConformance,
        ];
    }
}
