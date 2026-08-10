<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthDynamicClientRegistrationResult;

interface OAuthClientRegistrationResultSerializerInterface
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(
        OAuthDynamicClientRegistrationResult $result
    ): array;
}
