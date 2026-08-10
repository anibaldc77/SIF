<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiAuthorizationFlowAssessment;
use Sif\Foundation\Security\Fapi\FapiAuthorizationRequestSecurityRequirements;
use Sif\Foundation\Security\OAuth\Advanced\OAuthPushedAuthorizationRequest;

interface FapiAuthorizationFlowPolicyInterface
{
    public function assess(
        OAuthPushedAuthorizationRequest $request,
        FapiAuthorizationRequestSecurityRequirements $requirements
    ): FapiAuthorizationFlowAssessment;
}
