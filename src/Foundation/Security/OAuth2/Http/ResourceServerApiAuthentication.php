<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Http;

use Sif\Foundation\Security\OAuth2\Authorization\OAuthAuthorizationContext;

final readonly class ResourceServerApiAuthentication
{
    public function __construct(
        private ResourceServerAuthenticationResult $authentication,
        private ?OAuthAuthorizationContext $authorizationContext
    ) {
    }

    public function authentication(): ResourceServerAuthenticationResult
    {
        return $this->authentication;
    }

    public function authorizationContext(): ?OAuthAuthorizationContext
    {
        return $this->authorizationContext;
    }
}
