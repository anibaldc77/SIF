<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpAuthorizationResponse;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpProtectedAuthorizationResponse;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpResponseProtectionContext;

interface OpenId4VpAuthorizationResponseProtectorInterface
{
    public function protect(
        OpenId4VpAuthorizationResponse $response,
        OpenId4VpResponseProtectionContext $context
    ): OpenId4VpProtectedAuthorizationResponse;
}
