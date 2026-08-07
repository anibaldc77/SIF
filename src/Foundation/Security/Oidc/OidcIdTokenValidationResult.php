<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

final readonly class OidcIdTokenValidationResult
{
    public function __construct(
        private OidcFederatedIdentity $identity,
        private OidcIdToken $idToken
    ) {
    }

    public function identity(): OidcFederatedIdentity
    {
        return $this->identity;
    }

    public function idToken(): OidcIdToken
    {
        return $this->idToken;
    }
}
