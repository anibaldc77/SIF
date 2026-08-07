<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth2\AccessToken;
use Sif\Foundation\Security\OAuth2\Introspection\TokenIntrospectionResult;

interface TokenIntrospectorInterface
{
    public function introspect(
        AccessToken $token
    ): TokenIntrospectionResult;
}
