<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Advanced\OAuthPushedAuthorizationRequestUri;

interface OAuthPushedAuthorizationRequestUriGeneratorInterface
{
    public function generate(): OAuthPushedAuthorizationRequestUri;
}
