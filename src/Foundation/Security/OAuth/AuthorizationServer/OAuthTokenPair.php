<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

final readonly class OAuthTokenPair
{
    public function __construct(
        private OAuthAccessToken $accessToken,
        private ?OAuthRefreshToken $refreshToken = null
    ) {
    }

    public function accessToken(): OAuthAccessToken
    {
        return $this->accessToken;
    }

    public function refreshToken(): ?OAuthRefreshToken
    {
        return $this->refreshToken;
    }
}
