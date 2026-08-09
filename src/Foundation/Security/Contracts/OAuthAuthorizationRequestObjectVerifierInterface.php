<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Advanced\OAuthAuthorizationRequestObjectVerificationResult;

interface OAuthAuthorizationRequestObjectVerifierInterface
{
    public function verify(
        string $serialized
    ): OAuthAuthorizationRequestObjectVerificationResult;
}
