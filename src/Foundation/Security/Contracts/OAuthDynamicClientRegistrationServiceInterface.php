<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationMetadata;
use Sif\Foundation\Security\OAuth\Metadata\OAuthDynamicClientRegistrationResult;

interface OAuthDynamicClientRegistrationServiceInterface
{
    public function register(
        OAuthClientRegistrationMetadata $metadata
    ): OAuthClient;

    public function registerWithResult(
        OAuthClientRegistrationMetadata $metadata
    ): OAuthDynamicClientRegistrationResult;
}
