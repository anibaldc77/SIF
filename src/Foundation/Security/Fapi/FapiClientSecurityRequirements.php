<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

final readonly class FapiClientSecurityRequirements
{
    /** @param list<string> $allowedTokenEndpointAuthMethods */
    public function __construct(
        private bool $requireConfidentialClient = true,
        private bool $requirePkce = true,
        private bool $requirePar = true,
        private bool $requireSenderConstrainedTokens = true,
        private array $allowedTokenEndpointAuthMethods = [
            'private_key_jwt',
            'tls_client_auth',
            'self_signed_tls_client_auth',
        ]
    ) {}

    public function requireConfidentialClient(): bool { return $this->requireConfidentialClient; }
    public function requirePkce(): bool { return $this->requirePkce; }
    public function requirePar(): bool { return $this->requirePar; }
    public function requireSenderConstrainedTokens(): bool { return $this->requireSenderConstrainedTokens; }

    /** @return list<string> */
    public function allowedTokenEndpointAuthMethods(): array { return $this->allowedTokenEndpointAuthMethods; }
}
