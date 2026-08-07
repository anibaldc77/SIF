<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

final readonly class OidcTokenExchangeRequest
{
    public function __construct(
        private OidcAuthorizationCode $authorizationCode,
        private OidcClientRegistration $registration,
        private PkceCodeVerifier $codeVerifier,
        private ?OidcClientSecret $clientSecret = null
    ) {
    }

    public function authorizationCode(): OidcAuthorizationCode
    {
        return $this->authorizationCode;
    }

    public function registration(): OidcClientRegistration
    {
        return $this->registration;
    }

    public function codeVerifier(): PkceCodeVerifier
    {
        return $this->codeVerifier;
    }

    public function clientSecret(): ?OidcClientSecret
    {
        return $this->clientSecret;
    }
}
