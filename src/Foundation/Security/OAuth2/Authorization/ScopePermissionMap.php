<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Authorization;

use InvalidArgumentException;
use Sif\Foundation\Security\Authorization\Permission\PermissionIdentifier;
use Sif\Foundation\Security\Authorization\Permission\PermissionSet;
use Sif\Foundation\Security\OAuth2\ScopeSet;

final readonly class ScopePermissionMap
{
    /** @var array<string, PermissionSet> */
    private array $mapping;

    /**
     * @param array<string, PermissionSet> $mapping
     */
    public function __construct(array $mapping)
    {
        foreach ($mapping as $scope => $_permissions) {
            if (
                $scope === ''
                || strlen($scope) > 160
                || preg_match('/^[\x21\x23-\x5B\x5D-\x7E]+$/', $scope) !== 1
            ) {
                throw new InvalidArgumentException(
                    'OAuth scope mapping key is invalid.'
                );
            }
        }

        ksort($mapping);
        $this->mapping = $mapping;
    }

    public function resolve(ScopeSet $scopes): PermissionSet
    {
        $permissions = [];

        foreach ($scopes->values() as $scope) {
            $mapped = $this->mapping[$scope] ?? null;

            if ($mapped === null) {
                continue;
            }

            foreach ($mapped->values() as $permission) {
                $permissions[$permission] = new PermissionIdentifier($permission);
            }
        }

        ksort($permissions);

        return new PermissionSet(array_values($permissions));
    }
}
