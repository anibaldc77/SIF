<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Policy;

use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeBag;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class CompositeAuthorizationPolicyEvaluator
{
    public function __construct(
        private CompositeAuthorizationPolicy $policy
    ) {
    }

    public function evaluate(
        AuthenticatedPrincipal $principal,
        AuthorizationAttributeBag $resource = new AuthorizationAttributeBag(),
        AuthorizationAttributeBag $environment = new AuthorizationAttributeBag()
    ): CompositeAuthorizationEvaluation {
        if (!$this->policy->isSatisfiedBy($principal, $resource, $environment)) {
            return CompositeAuthorizationEvaluation::rejected(
                'Composite RBAC/ABAC requirements were not satisfied.'
            );
        }

        return CompositeAuthorizationEvaluation::satisfied(
            'Composite RBAC/ABAC requirements were satisfied.'
        );
    }
}
