<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationRequest;

interface OAuthAuthorizationRequestValidatorInterface
{
    public function validate(
        OAuthAuthorizationRequest $request
    ): void;
}
