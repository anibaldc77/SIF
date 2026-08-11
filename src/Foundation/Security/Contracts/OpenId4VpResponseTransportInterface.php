<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpAuthorizationResponse;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpProtectedAuthorizationResponse;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpResponseDestination;

interface OpenId4VpResponseTransportInterface
{
    public function send(
        OpenId4VpResponseDestination $destination,
        OpenId4VpAuthorizationResponse|OpenId4VpProtectedAuthorizationResponse $response
    ): void;
}
