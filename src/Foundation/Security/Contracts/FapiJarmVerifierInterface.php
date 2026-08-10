<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiSignedAuthorizationResponse;

interface FapiJarmVerifierInterface
{
    public function verify(
        string $serializedResponse,
        string $expectedIssuer,
        string $expectedAudience
    ): FapiSignedAuthorizationResponse;
}
