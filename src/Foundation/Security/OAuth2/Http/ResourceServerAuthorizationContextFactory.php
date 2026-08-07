<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Http;

use Sif\Foundation\Security\OAuth2\Authorization\OAuthAccessTokenAuthorizationAttributes;
use Sif\Foundation\Security\OAuth2\Authorization\OAuthAuthorizationContext;
use Sif\Foundation\Security\OAuth2\Authorization\ScopePermissionMap;

final readonly class ResourceServerAuthorizationContextFactory
{
    public function __construct(
        private ScopePermissionMap $permissionMap,
        private OAuthAccessTokenAuthorizationAttributes $attributes
    ) {
    }

    public function create(
        ResourceServerAuthenticationResult $authentication
    ): ?OAuthAuthorizationContext {
        $token = $authentication->token();

        if (!$authentication->isAuthenticated() || $token === null) {
            return null;
        }

        return new OAuthAuthorizationContext(
            $token,
            $this->permissionMap,
            $this->attributes
        );
    }
}
