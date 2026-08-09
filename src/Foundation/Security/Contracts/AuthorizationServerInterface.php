<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationRequest;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthTokenRequest;

interface AuthorizationServerInterface
{
    public function authorize(OAuthAuthorizationRequest $request): mixed;

    public function token(OAuthTokenRequest $request): mixed;
}
