<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth2\Jwks\Jwk;
use Sif\Foundation\Security\OAuth2\Jwt\ParsedJwt;

interface JwkSignatureVerifierInterface
{
    public function verify(
        ParsedJwt $jwt,
        Jwk $key
    ): bool;
}
