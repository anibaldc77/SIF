<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Authorization;

use Sif\Foundation\Security\Authorization\Permission\PermissionSet;
use Sif\Foundation\Security\Contracts\PermissionResolverInterface;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\OAuth2\ValidatedAccessToken;

final readonly class OAuthPermissionResolver implements PermissionResolverInterface
{
    public function __construct(
        private ValidatedAccessToken $token,
        private ScopePermissionMap $mapping
    ) {
    }

    public function resolve(
        AuthenticatedPrincipal $principal
    ): PermissionSet {
        if (
            $principal->identity()->id()->value()
            !== $this->token->subject()->value()
        ) {
            return new PermissionSet();
        }

        return $this->mapping->resolve($this->token->scopes());
    }
}
