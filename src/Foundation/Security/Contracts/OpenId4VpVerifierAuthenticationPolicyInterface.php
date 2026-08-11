<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpVerifierAuthenticationResult;

interface OpenId4VpVerifierAuthenticationPolicyInterface
{
    public function accepts(
        OpenId4VpVerifierAuthenticationResult $result
    ): bool;
}
