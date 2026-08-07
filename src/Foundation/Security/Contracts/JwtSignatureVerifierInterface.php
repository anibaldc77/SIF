<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth2\Jwt\ParsedJwt;

interface JwtSignatureVerifierInterface
{
    public function verify(ParsedJwt $jwt): bool;
}
