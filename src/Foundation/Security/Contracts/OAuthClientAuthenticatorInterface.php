<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientAuthenticationRequest;

interface OAuthClientAuthenticatorInterface
{
    public function authenticate(
        OAuthClientAuthenticationRequest $request
    ): OAuthClient;
}
