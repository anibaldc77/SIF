<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Advanced;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationRequest;

final readonly class OAuthAuthorizationRequestObjectVerificationResult
{
    public function __construct(
        private OAuthAuthorizationRequest $authorizationRequest,
        private string $issuer,
        private string $audience,
        private string $tokenId
    ) {
    }

    public function authorizationRequest(): OAuthAuthorizationRequest
    {
        return $this->authorizationRequest;
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function audience(): string
    {
        return $this->audience;
    }

    public function tokenId(): string
    {
        return $this->tokenId;
    }
}
