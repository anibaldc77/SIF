<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthTokenIntrospection;

interface OAuthTokenIntrospectorInterface
{
    public function introspect(
        string $token
    ): OAuthTokenIntrospection;
}
