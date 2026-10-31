<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipAuthorizationAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipAuthorizationContext;

interface OpenId4VcHaipAuthorizationConformancePolicyInterface
{
    public function assess(
        OpenId4VcHaipAuthorizationContext $context
    ): OpenId4VcHaipAuthorizationAssessment;
}
