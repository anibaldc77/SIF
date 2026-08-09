<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Advanced;

use InvalidArgumentException;

final readonly class OAuthPushedAuthorizationRequestUri
{
    public function __construct(private string $value)
    {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException(
                'OAuth pushed authorization request URI is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
