<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OAuth\Metadata;
final readonly class OAuthMetadataProductCapabilities
{
    public function __construct(
        private bool $authorizationServerMetadata = true,
        private bool $protectedResourceMetadata = true,
        private bool $dynamicClientRegistration = true,
        private bool $clientRegistrationManagement = true,
        private bool $softwareStatements = true,
        private bool $issuerValidation = true,
        private bool $discoveryResolution = true
    ) {}
    public function authorizationServerMetadata(): bool { return $this->authorizationServerMetadata; }
    public function protectedResourceMetadata(): bool { return $this->protectedResourceMetadata; }
    public function dynamicClientRegistration(): bool { return $this->dynamicClientRegistration; }
    public function clientRegistrationManagement(): bool { return $this->clientRegistrationManagement; }
    public function softwareStatements(): bool { return $this->softwareStatements; }
    public function issuerValidation(): bool { return $this->issuerValidation; }
    public function discoveryResolution(): bool { return $this->discoveryResolution; }
    /** @return array<string,bool> */
    public function toArray(): array { return [
        'authorization_server_metadata'=>$this->authorizationServerMetadata,
        'protected_resource_metadata'=>$this->protectedResourceMetadata,
        'dynamic_client_registration'=>$this->dynamicClientRegistration,
        'client_registration_management'=>$this->clientRegistrationManagement,
        'software_statements'=>$this->softwareStatements,
        'issuer_validation'=>$this->issuerValidation,
        'discovery_resolution'=>$this->discoveryResolution,
    ]; }
}
