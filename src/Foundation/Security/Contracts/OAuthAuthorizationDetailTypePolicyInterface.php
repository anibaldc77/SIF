<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Advanced\OAuthAuthorizationDetail;

interface OAuthAuthorizationDetailTypePolicyInterface
{
    public function validate(
        OAuthAuthorizationDetail $detail
    ): void;
}
