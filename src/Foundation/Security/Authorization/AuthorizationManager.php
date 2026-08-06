<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization;

use Throwable;

final readonly class AuthorizationManager
{
    public function __construct(private AuthorizationPolicyRegistry $registry)
    {
    }

    public function decide(AuthorizationRequest $request): AuthorizationDecision
    {
        $policies = $this->registry->applicableTo($request);
        if ($policies === []) {
            return AuthorizationDecision::deny(AuthorizationFailureReason::NO_APPLICABLE_POLICY);
        }

        foreach ($policies as $policy) {
            try {
                $decision = $policy->decide($request);
            } catch (Throwable) {
                return AuthorizationDecision::deny(AuthorizationFailureReason::TECHNICAL_FAILURE);
            }

            if (!$decision->isAllowed()) {
                return $decision;
            }
        }

        return AuthorizationDecision::allow();
    }
}
