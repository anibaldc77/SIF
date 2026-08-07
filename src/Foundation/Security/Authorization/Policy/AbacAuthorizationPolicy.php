<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Policy;

use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeBag;
use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeContext;
use Sif\Foundation\Security\Authorization\Requirement\ContextualRequirementSet;
use Sif\Foundation\Security\Contracts\AuthorizationAttributeProviderInterface;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class AbacAuthorizationPolicy
{
    public function __construct(
        private AuthorizationAttributeProviderInterface $subjectAttributes,
        private ContextualRequirementSet $requirements
    ) {
    }

    public function isSatisfiedBy(
        AuthenticatedPrincipal $principal,
        AuthorizationAttributeBag $resource = new AuthorizationAttributeBag(),
        AuthorizationAttributeBag $environment = new AuthorizationAttributeBag()
    ): bool {
        return $this->requirements->isSatisfiedBy(
            $principal,
            new AuthorizationAttributeContext(
                $this->subjectAttributes->provide($principal),
                $resource,
                $environment
            )
        );
    }
}
