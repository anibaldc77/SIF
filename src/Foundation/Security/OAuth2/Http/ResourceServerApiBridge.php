<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Http;

use DateTimeImmutable;

final readonly class ResourceServerApiBridge
{
    public function __construct(
        private OAuth2ResourceServerAuthenticator $authenticator,
        private ResourceServerAuthorizationContextFactory $authorizationContextFactory
    ) {
    }

    public function authenticate(
        string $authorizationHeader,
        DateTimeImmutable $now
    ): ResourceServerApiAuthentication
    {
        $authentication = $this->authenticator->authenticate(
            $authorizationHeader,
            $now
        );

        return new ResourceServerApiAuthentication(
            $authentication,
            $this->authorizationContextFactory->create($authentication)
        );
    }
}
