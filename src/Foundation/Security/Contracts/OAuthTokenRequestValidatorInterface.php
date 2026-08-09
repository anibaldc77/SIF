<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthTokenRequest;

interface OAuthTokenRequestValidatorInterface
{
    public function validate(
        OAuthTokenRequest $request
    ): void;
}
