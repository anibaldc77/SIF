<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Policy;

use Sif\Foundation\Security\Authorization\Permission\PrincipalAuthorizationGrantResolver;
use Sif\Foundation\Security\Authorization\Requirement\AuthorizationRequirementSet;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class RbacAuthorizationPolicy
{
    public function __construct(
        private PrincipalAuthorizationGrantResolver $grantResolver,
        private AuthorizationRequirementSet $requirements
    ) {
    }

    public function isSatisfiedBy(
        AuthenticatedPrincipal $principal
    ): bool {
        return $this->requirements->isSatisfiedBy(
            $principal,
            $this->grantResolver->resolve($principal)
        );
    }
}
