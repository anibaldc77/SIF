<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRefreshToken;

interface OAuthRefreshTokenRotatorInterface
{
    public function rotate(
        OAuthRefreshToken $current
    ): OAuthRefreshToken;
}
