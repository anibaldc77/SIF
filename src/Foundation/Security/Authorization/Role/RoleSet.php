<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Role;

final readonly class RoleSet
{
    /** @var array<string, RoleIdentifier> */
    private array $roles;

    /**
     * @param iterable<RoleIdentifier> $roles
     */
    public function __construct(iterable $roles = [])
    {
        $normalized = [];

        foreach ($roles as $role) {
            $normalized[$role->value()] = $role;
        }

        ksort($normalized);

        $this->roles = $normalized;
    }

    public function contains(RoleIdentifier $role): bool
    {
        return isset($this->roles[$role->value()]);
    }

    public function count(): int
    {
        return count($this->roles);
    }

    /** @return list<RoleIdentifier> */
    public function all(): array
    {
        return array_values($this->roles);
    }

    /** @return list<string> */
    public function values(): array
    {
        return array_keys($this->roles);
    }
}
