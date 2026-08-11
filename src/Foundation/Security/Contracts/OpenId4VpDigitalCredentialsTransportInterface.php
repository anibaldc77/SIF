<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpDigitalCredentialsRequest;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpDigitalCredentialsResponse;

interface OpenId4VpDigitalCredentialsTransportInterface
{
    public function exchange(
        OpenId4VpDigitalCredentialsRequest $request
    ): OpenId4VpDigitalCredentialsResponse;
}
