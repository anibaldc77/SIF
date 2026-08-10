<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Advanced\OAuthDPoPVerificationResult;

interface FapiDPoPPolicyInterface
{
    public function validate(
        OAuthDPoPVerificationResult $proof
    ): void;
}
