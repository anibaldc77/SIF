<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Authorization;

use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeBag;
use Sif\Foundation\Security\OAuth2\ValidatedAccessToken;

final readonly class OAuthAuthorizationContext
{
    public function __construct(
        private ValidatedAccessToken $token,
        private ScopePermissionMap $permissionMap,
        private OAuthAccessTokenAuthorizationAttributes $attributes
    ) {
    }

    public function permissions(): \Sif\Foundation\Security\Authorization\Permission\PermissionSet
    {
        return $this->permissionMap->resolve($this->token->scopes());
    }

    public function subjectAttributes(): AuthorizationAttributeBag
    {
        return $this->attributes->from($this->token);
    }
}
