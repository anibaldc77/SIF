<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Requirement;

use Sif\Foundation\Security\Authorization\Permission\PermissionIdentifier;
use Sif\Foundation\Security\Authorization\Permission\ResolvedAuthorizationGrants;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class PermissionRequirement implements AuthorizationRequirementInterface
{
    public function __construct(
        private PermissionIdentifier $permission
    ) {
    }

    public function permission(): PermissionIdentifier
    {
        return $this->permission;
    }

    public function isSatisfiedBy(
        AuthenticatedPrincipal $principal,
        ResolvedAuthorizationGrants $grants
    ): bool {
        return $grants->permissions()->contains($this->permission);
    }
}
