<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Permission;

use Sif\Foundation\Security\Authorization\Role\RoleHierarchy;
use Sif\Foundation\Security\Authorization\Role\RoleSet;

final readonly class EffectivePermissionResolver
{
    public function __construct(private RoleHierarchy $hierarchy)
    {
    }

    public function resolve(
        RoleSet $roles,
        PermissionSet $directPermissions = new PermissionSet()
    ): PermissionSet {
        $permissions = [];

        foreach ($directPermissions->all() as $permission) {
            $permissions[$permission->value()] = $permission;
        }

        foreach ($roles->all() as $role) {
            foreach ($this->hierarchy->expand($role) as $effectiveRole) {
                $definition = $this->hierarchy->find($effectiveRole);

                if ($definition === null) {
                    continue;
                }

                foreach ($definition->permissions()->all() as $permission) {
                    $permissions[$permission->value()] = $permission;
                }
            }
        }

        ksort($permissions);

        return new PermissionSet(array_values($permissions));
    }
}
