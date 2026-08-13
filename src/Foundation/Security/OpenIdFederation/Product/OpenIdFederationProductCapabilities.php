<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OpenIdFederation\Product;
final readonly class OpenIdFederationProductCapabilities
{
    public function entityStatements(): bool { return true; }
    public function semanticValidation(): bool { return true; }
    public function fetchListResolveProtocols(): bool { return true; }
    public function metadataPolicy(): bool { return true; }
    public function trustMarks(): bool { return true; }
    public function federationTrustChains(): bool { return true; }
    public function runtimeFreshnessAndResilience(): bool { return true; }
    public function oidcWalletInteroperability(): bool { return true; }
    /** @return array<string, bool> */
    public function toArray(): array
    {
        return [
            'entity_statements'=>$this->entityStatements(),
            'semantic_validation'=>$this->semanticValidation(),
            'fetch_list_resolve_protocols'=>$this->fetchListResolveProtocols(),
            'metadata_policy'=>$this->metadataPolicy(),
            'trust_marks'=>$this->trustMarks(),
            'federation_trust_chains'=>$this->federationTrustChains(),
            'runtime_freshness_and_resilience'=>$this->runtimeFreshnessAndResilience(),
            'oidc_wallet_interoperability'=>$this->oidcWalletInteroperability(),
        ];
    }
}
