<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientCredentialsGrant;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthMachinePrincipal;

interface OAuthMachineIdentityResolverInterface
{
    public function resolve(
        OAuthClientCredentialsGrant $grant
    ): OAuthMachinePrincipal;
}
