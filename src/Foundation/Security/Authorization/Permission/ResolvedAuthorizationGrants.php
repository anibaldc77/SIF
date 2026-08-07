<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Permission;

use Sif\Foundation\Security\Authorization\Role\RoleSet;

final readonly class ResolvedAuthorizationGrants
{
    public function __construct(
        private RoleSet $roles,
        private PermissionSet $permissions
    ) {
    }

    public function roles(): RoleSet
    {
        return $this->roles;
    }

    public function permissions(): PermissionSet
    {
        return $this->permissions;
    }
}
