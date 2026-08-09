<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use InvalidArgumentException;

final readonly class OAuthPkceVerifier
{
    public function __construct(private string $value)
    {
        $length = strlen($this->value);

        if (
            $length < 43
            || $length > 128
            || preg_match('/^[A-Za-z0-9\-._~]+$/', $this->value) !== 1
        ) {
            throw new InvalidArgumentException(
                'OAuth PKCE code verifier is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
