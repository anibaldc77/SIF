<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OpenIdFederation\Product;
use InvalidArgumentException;
final readonly class OpenIdFederationProductProfile
{
    public function __construct(private string $name, private OpenIdFederationProductCapabilities $capabilities)
    {
        if (trim($this->name)==='') { throw new InvalidArgumentException('OpenID Federation product profile name is invalid.'); }
    }
    public function name(): string { return $this->name; }
    public function capabilities(): OpenIdFederationProductCapabilities { return $this->capabilities; }
    public function requireVerifiedEntityStatements(): bool { return true; }
    public function requireValidatedMetadataPolicy(): bool { return true; }
    public function requireValidatedFederationTrustChain(): bool { return true; }
    public function requireCurrentRuntimeEvidence(): bool { return true; }
    public function requireCredentialTrustEnforcement(): bool { return true; }
}
