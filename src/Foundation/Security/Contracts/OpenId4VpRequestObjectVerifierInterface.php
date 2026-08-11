<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpRequestObject;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpVerifierAuthenticationResult;

interface OpenId4VpRequestObjectVerifierInterface
{
    public function verify(
        OpenId4VpRequestObject $requestObject
    ): OpenId4VpVerifierAuthenticationResult;
}
