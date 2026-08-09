<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Advanced\OAuthRichAuthorizationRequest;

interface OAuthAuthorizationDetailsNormalizerInterface
{
    /**
     * @param list<array<string, mixed>> $authorizationDetails
     */
    public function normalize(
        array $authorizationDetails
    ): OAuthRichAuthorizationRequest;
}
