<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Policy;

use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeBag;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class CompositeAuthorizationPolicy
{
    public function __construct(
        private RbacAuthorizationPolicy $rbac,
        private AbacAuthorizationPolicy $abac
    ) {
    }

    public function isSatisfiedBy(
        AuthenticatedPrincipal $principal,
        AuthorizationAttributeBag $resource = new AuthorizationAttributeBag(),
        AuthorizationAttributeBag $environment = new AuthorizationAttributeBag()
    ): bool {
        return $this->rbac->isSatisfiedBy($principal)
            && $this->abac->isSatisfiedBy($principal, $resource, $environment);
    }
}
