<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpAuthorizationResponse;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpDigitalCredentialsResponse;

interface OpenId4VpDigitalCredentialsResponseResolverInterface
{
    public function resolve(
        OpenId4VpDigitalCredentialsResponse $response
    ): OpenId4VpAuthorizationResponse;
}
