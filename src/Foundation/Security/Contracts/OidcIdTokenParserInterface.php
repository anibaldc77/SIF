<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth2\Jwt\ParsedJwt;
use Sif\Foundation\Security\Oidc\OidcIdToken;

interface OidcIdTokenParserInterface
{
    public function parse(
        OidcIdToken $idToken
    ): ParsedJwt;
}
