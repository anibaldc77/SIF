<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Advanced\OAuthAuthorizationRequestObject;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationRequest;

interface OAuthAuthorizationRequestObjectSignerInterface
{
    public function sign(
        OAuthAuthorizationRequest $request
    ): OAuthAuthorizationRequestObject;
}
