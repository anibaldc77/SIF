<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Role;

use Sif\Foundation\Security\Authorization\Permission\PermissionSet;

final readonly class RoleDefinition
{
    public function __construct(
        private RoleIdentifier $id,
        private PermissionSet $permissions,
        private RoleSet $inherits = new RoleSet()
    ) {
    }

    public function id(): RoleIdentifier
    {
        return $this->id;
    }

    public function permissions(): PermissionSet
    {
        return $this->permissions;
    }

    public function inherits(): RoleSet
    {
        return $this->inherits;
    }
}
