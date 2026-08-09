<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

final readonly class OAuthPkceChallenge
{
    public function __construct(
        private string $value,
        private OAuthPkceMethod $method
    ) {
    }

    public function value(): string
    {
        return $this->value;
    }

    public function method(): OAuthPkceMethod
    {
        return $this->method;
    }
}
