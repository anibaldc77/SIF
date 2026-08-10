<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationMetadata;

interface OAuthClientRegistrationPolicyInterface
{
    public function authorize(
        OAuthClientRegistrationMetadata $metadata
    ): void;
}
