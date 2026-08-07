<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth2\AccessToken;
use Sif\Foundation\Security\OAuth2\Jwt\ParsedJwt;

interface JwtParserInterface
{
    public function parse(AccessToken $token): ParsedJwt;
}
