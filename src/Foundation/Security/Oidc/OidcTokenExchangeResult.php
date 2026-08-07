<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use Sif\Foundation\Security\OAuth2\AccessToken;

final readonly class OidcTokenExchangeResult
{
    public function __construct(
        private OidcIdToken $idToken,
        private ?AccessToken $accessToken = null
    ) {
    }

    public function idToken(): OidcIdToken
    {
        return $this->idToken;
    }

    public function accessToken(): ?AccessToken
    {
        return $this->accessToken;
    }
}
