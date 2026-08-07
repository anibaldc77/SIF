<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Requirement;

use Sif\Foundation\Security\Authorization\Permission\ResolvedAuthorizationGrants;
use Sif\Foundation\Security\Authorization\Role\RoleIdentifier;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class RoleRequirement implements AuthorizationRequirementInterface
{
    public function __construct(
        private RoleIdentifier $role
    ) {
    }

    public function role(): RoleIdentifier
    {
        return $this->role;
    }

    public function isSatisfiedBy(
        AuthenticatedPrincipal $principal,
        ResolvedAuthorizationGrants $grants
    ): bool {
        return $grants->roles()->contains($this->role);
    }
}
