<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OAuth\Metadata;
use InvalidArgumentException;
final readonly class OAuthMetadataProductProfile
{
    public function __construct(
        private string $name,
        private OAuthMetadataProductCapabilities $capabilities,
        private bool $requireExactIssuerMatch = true,
        private bool $requireHttpsDiscovery = true,
        private bool $requireFreshMetadata = true,
        private bool $allowDynamicRegistration = true
    ) { if (trim($this->name)==='') throw new InvalidArgumentException('OAuth metadata product profile name is invalid.'); }
    public function name(): string { return $this->name; }
    public function capabilities(): OAuthMetadataProductCapabilities { return $this->capabilities; }
    public function requireExactIssuerMatch(): bool { return $this->requireExactIssuerMatch; }
    public function requireHttpsDiscovery(): bool { return $this->requireHttpsDiscovery; }
    public function requireFreshMetadata(): bool { return $this->requireFreshMetadata; }
    public function allowDynamicRegistration(): bool { return $this->allowDynamicRegistration; }
}
