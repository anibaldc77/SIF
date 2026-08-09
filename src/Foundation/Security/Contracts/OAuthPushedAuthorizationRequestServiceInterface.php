<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Advanced\OAuthPushedAuthorizationRequest;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationRequest;

interface OAuthPushedAuthorizationRequestServiceInterface
{
    public function push(
        OAuthAuthorizationRequest $request
    ): OAuthPushedAuthorizationRequest;
}
