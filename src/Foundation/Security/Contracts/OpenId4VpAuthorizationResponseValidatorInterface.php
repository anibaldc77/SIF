<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpAuthorizationResponse;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationContext;

interface OpenId4VpAuthorizationResponseValidatorInterface
{
    public function validate(
        OpenId4VpAuthorizationResponse $response,
        OpenId4VpPresentationContext $context
    ): OpenId4VpPresentationAssessment;
}
