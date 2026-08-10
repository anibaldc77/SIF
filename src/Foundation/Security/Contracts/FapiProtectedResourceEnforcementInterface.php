<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiResourceServerAssessment;
use Sif\Foundation\Security\OAuth\Advanced\OAuthProtectedResourceRequest;
use Sif\Foundation\Security\OAuth\Advanced\OAuthProtectedResourceValidationResult;

interface FapiProtectedResourceEnforcementInterface
{
    public function enforce(
        OAuthProtectedResourceRequest $request,
        OAuthProtectedResourceValidationResult $validation
    ): FapiResourceServerAssessment;
}
