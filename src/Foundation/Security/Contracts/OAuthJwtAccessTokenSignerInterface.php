<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthJwtClaims;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthSignedAccessToken;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthSigningKey;

interface OAuthJwtAccessTokenSignerInterface
{
    public function sign(
        OAuthJwtClaims $claims,
        OAuthSigningKey $key
    ): OAuthSignedAccessToken;
}
