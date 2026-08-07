<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Requirement;

use Sif\Foundation\Security\Authorization\Permission\ResolvedAuthorizationGrants;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

interface AuthorizationRequirementInterface
{
    public function isSatisfiedBy(
        AuthenticatedPrincipal $principal,
        ResolvedAuthorizationGrants $grants
    ): bool;
}
