<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Requirement;

use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeContext;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

interface ContextualAuthorizationRequirementInterface
{
    public function isSatisfiedBy(
        AuthenticatedPrincipal $principal,
        AuthorizationAttributeContext $attributes
    ): bool;
}
