<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Advanced\OAuthRichAuthorizationRequest;

interface OAuthAuthorizationDetailsValidatorInterface
{
    public function validate(
        OAuthRichAuthorizationRequest $request
    ): void;
}
