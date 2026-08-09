<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

final readonly class OAuthClientCredential
{
    public function __construct(
        private OAuthClientAuthenticationMethod $method,
        private string $value
    ) {
    }

    public function method(): OAuthClientAuthenticationMethod
    {
        return $this->method;
    }

    public function value(): string
    {
        return $this->value;
    }
}
