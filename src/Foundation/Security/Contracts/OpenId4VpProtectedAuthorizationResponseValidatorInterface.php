<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpProtectedAuthorizationResponse;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpResponseProtectionAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpResponseProtectionContext;

interface OpenId4VpProtectedAuthorizationResponseValidatorInterface
{
    public function validate(
        OpenId4VpProtectedAuthorizationResponse $response,
        OpenId4VpResponseProtectionContext $context
    ): OpenId4VpResponseProtectionAssessment;
}
