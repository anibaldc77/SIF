<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use InvalidArgumentException;

final readonly class OidcNonce
{
    public function __construct(private string $value)
    {
        if (
            strlen($this->value) < 32
            || strlen($this->value) > 512
            || preg_match('/^[A-Za-z0-9_-]+$/', $this->value) !== 1
        ) {
            throw new InvalidArgumentException(
                'OIDC nonce must be bounded and URL-safe.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
