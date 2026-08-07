<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Policy;

use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeBag;
use Sif\Foundation\Security\Authorization\AuthorizationDecision;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class AdvancedAuthorizationService
{
    public function __construct(
        private CompositeAuthorizationPolicyEvaluator $evaluator,
        private ExistingAuthorizationDecisionAdapter $decisionAdapter
    ) {
    }

    public function decide(
        AuthenticatedPrincipal $principal,
        AuthorizationAttributeBag $resource = new AuthorizationAttributeBag(),
        AuthorizationAttributeBag $environment = new AuthorizationAttributeBag()
    ): AuthorizationDecision {
        return $this->decisionAdapter->adapt(
            $this->evaluator->evaluate($principal, $resource, $environment)
        );
    }
}
