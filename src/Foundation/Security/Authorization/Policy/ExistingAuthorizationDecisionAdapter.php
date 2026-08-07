<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Policy;

use Sif\Foundation\Security\Authorization\AuthorizationDecision;
use Sif\Foundation\Security\Authorization\AuthorizationFailureReason;

final readonly class ExistingAuthorizationDecisionAdapter
{
    public function adapt(
        CompositeAuthorizationEvaluation $evaluation
    ): AuthorizationDecision {
        return $evaluation->isSatisfied()
            ? AuthorizationDecision::allow()
            : AuthorizationDecision::deny(
                AuthorizationFailureReason::NOT_AUTHORIZED
            );
    }
}
