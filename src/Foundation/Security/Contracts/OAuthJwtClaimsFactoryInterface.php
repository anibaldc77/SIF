<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAccessToken;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthJwtClaims;

interface OAuthJwtClaimsFactoryInterface
{
    public function create(
        OAuthAccessToken $token,
        string $subject
    ): OAuthJwtClaims;
}
