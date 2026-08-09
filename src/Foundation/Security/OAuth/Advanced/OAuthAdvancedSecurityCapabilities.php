<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Advanced;

final readonly class OAuthAdvancedSecurityCapabilities
{
    public function __construct(
        private bool $pushedAuthorizationRequests = true,
        private bool $richAuthorizationRequests = true,
        private bool $jwtSecuredAuthorizationRequests = true,
        private bool $dpop = true,
        private bool $senderConstrainedAccessTokens = true,
        private bool $resourceServerEnforcement = true
    ) {
    }

    public function pushedAuthorizationRequests(): bool { return $this->pushedAuthorizationRequests; }
    public function richAuthorizationRequests(): bool { return $this->richAuthorizationRequests; }
    public function jwtSecuredAuthorizationRequests(): bool { return $this->jwtSecuredAuthorizationRequests; }
    public function dpop(): bool { return $this->dpop; }
    public function senderConstrainedAccessTokens(): bool { return $this->senderConstrainedAccessTokens; }
    public function resourceServerEnforcement(): bool { return $this->resourceServerEnforcement; }

    /** @return array<string, bool> */
    public function toArray(): array
    {
        return [
            'par' => $this->pushedAuthorizationRequests,
            'rar' => $this->richAuthorizationRequests,
            'jar' => $this->jwtSecuredAuthorizationRequests,
            'dpop' => $this->dpop,
            'sender_constrained_access_tokens' => $this->senderConstrainedAccessTokens,
            'resource_server_enforcement' => $this->resourceServerEnforcement,
        ];
    }
}
