<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpAuthorizationRequest;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationContext;

interface OpenId4VpAuthorizationRequestValidatorInterface
{
    public function validate(
        OpenId4VpAuthorizationRequest $request,
        OpenId4VpPresentationContext $context
    ): OpenId4VpPresentationAssessment;
}
