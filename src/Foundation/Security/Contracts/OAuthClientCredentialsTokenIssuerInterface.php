<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientCredentialsGrant;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthMachinePrincipal;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthTokenPair;

interface OAuthClientCredentialsTokenIssuerInterface
{
    public function issue(
        OAuthClientCredentialsGrant $grant,
        OAuthMachinePrincipal $principal
    ): OAuthTokenPair;
}
