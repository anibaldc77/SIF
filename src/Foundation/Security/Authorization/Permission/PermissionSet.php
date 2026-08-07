<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Permission;

final readonly class PermissionSet
{
    /** @var array<string, PermissionIdentifier> */
    private array $permissions;

    /**
     * @param iterable<PermissionIdentifier> $permissions
     */
    public function __construct(iterable $permissions = [])
    {
        $normalized = [];

        foreach ($permissions as $permission) {
            $normalized[$permission->value()] = $permission;
        }

        ksort($normalized);

        $this->permissions = $normalized;
    }

    public function contains(PermissionIdentifier $permission): bool
    {
        return isset($this->permissions[$permission->value()]);
    }

    public function count(): int
    {
        return count($this->permissions);
    }

    /** @return list<PermissionIdentifier> */
    public function all(): array
    {
        return array_values($this->permissions);
    }

    /**
     * @return list<string>
     */
    public function values(): array
    {
        return array_keys($this->permissions);
    }
}
