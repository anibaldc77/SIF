<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpAuthorizationRequest;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpDigitalCredentialsRequest;

interface OpenId4VpDigitalCredentialsRequestFactoryInterface
{
    public function create(
        OpenId4VpAuthorizationRequest $request
    ): OpenId4VpDigitalCredentialsRequest;
}
