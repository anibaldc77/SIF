<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpDigitalCredentialsAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpDigitalCredentialsContext;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpDigitalCredentialsRequest;

interface OpenId4VpDigitalCredentialsPolicyInterface
{
    public function assess(
        OpenId4VpDigitalCredentialsRequest $request,
        OpenId4VpDigitalCredentialsContext $context
    ): OpenId4VpDigitalCredentialsAssessment;
}
