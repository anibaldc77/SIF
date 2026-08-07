<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Permission;

use Sif\Foundation\Security\Contracts\PermissionResolverInterface;
use Sif\Foundation\Security\Contracts\RoleResolverInterface;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class PrincipalAuthorizationGrantResolver
{
    public function __construct(
        private RoleResolverInterface $roleResolver,
        private PermissionResolverInterface $permissionResolver,
        private EffectivePermissionResolver $effectivePermissions
    ) {
    }

    public function resolve(
        AuthenticatedPrincipal $principal
    ): ResolvedAuthorizationGrants {
        $roles = $this->roleResolver->resolve($principal);
        $direct = $this->permissionResolver->resolve($principal);

        return new ResolvedAuthorizationGrants(
            $roles,
            $this->effectivePermissions->resolve($roles, $direct)
        );
    }
}
