<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationCode;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthTokenPair;

interface OAuthTokenIssuerInterface
{
    public function issueFromAuthorizationCode(
        OAuthAuthorizationCode $code
    ): OAuthTokenPair;
}
