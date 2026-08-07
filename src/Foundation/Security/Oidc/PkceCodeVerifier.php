<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use InvalidArgumentException;

final readonly class PkceCodeVerifier
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
                'PKCE code verifier must follow RFC 7636 unreserved character and length requirements.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
