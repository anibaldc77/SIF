<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Role;

use InvalidArgumentException;

final readonly class RoleHierarchy
{
    /** @var array<string, RoleDefinition> */
    private array $definitions;

    /**
     * @param iterable<RoleDefinition> $definitions
     */
    public function __construct(iterable $definitions = [])
    {
        $normalized = [];

        foreach ($definitions as $definition) {
            $key = $definition->id()->value();

            if (isset($normalized[$key])) {
                throw new InvalidArgumentException(
                    sprintf('Duplicate role definition "%s".', $key)
                );
            }

            $normalized[$key] = $definition;
        }

        ksort($normalized);

        $this->assertNoCycles($normalized);

        $this->definitions = $normalized;
    }

    public function find(RoleIdentifier $role): ?RoleDefinition
    {
        return $this->definitions[$role->value()] ?? null;
    }

    /**
     * @return list<RoleIdentifier>
     */
    public function expand(RoleIdentifier $role): array
    {
        $result = [];
        $visited = [];

        $this->expandInto($role, $result, $visited);

        ksort($result);

        return array_values($result);
    }

    /**
     * @param array<string, RoleIdentifier> $result
     * @param array<string, bool> $visited
     */
    private function expandInto(
        RoleIdentifier $role,
        array &$result,
        array &$visited
    ): void {
        $key = $role->value();

        if (isset($visited[$key])) {
            return;
        }

        $visited[$key] = true;
        $result[$key] = $role;

        $definition = $this->definitions[$key] ?? null;
        if ($definition === null) {
            return;
        }

        foreach ($definition->inherits()->all() as $inherited) {
            $this->expandInto($inherited, $result, $visited);
        }
    }

    /**
     * @param array<string, RoleDefinition> $definitions
     */
    private function assertNoCycles(array $definitions): void
    {
        /** @var array<string, int> $state */
        $state = [];

        foreach ($definitions as $role => $_definition) {
            $this->visit($role, $definitions, $state);
        }
    }

    /**
     * @param array<string, RoleDefinition> $definitions
     * @param array<string, int> $state
     */
    private function visit(
        string $role,
        array $definitions,
        array &$state
    ): void {
        $current = $state[$role] ?? 0;

        if ($current === 2) {
            return;
        }

        if ($current === 1) {
            throw new InvalidArgumentException(
                sprintf('Role hierarchy contains a cycle at "%s".', $role)
            );
        }

        $state[$role] = 1;

        $definition = $definitions[$role] ?? null;
        if ($definition !== null) {
            foreach ($definition->inherits()->all() as $parent) {
                $parentValue = $parent->value();

                if (!isset($definitions[$parentValue])) {
                    continue;
                }

                $this->visit($parentValue, $definitions, $state);
            }
        }

        $state[$role] = 2;
    }
}
