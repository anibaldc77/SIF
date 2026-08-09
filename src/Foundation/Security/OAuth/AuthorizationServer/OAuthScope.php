<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use InvalidArgumentException;

final readonly class OAuthScope
{
    public function __construct(private string $value)
    {
        if (
            trim($this->value) === ''
            || preg_match('/\s/', $this->value) === 1
        ) {
            throw new InvalidArgumentException(
                'OAuth scope is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
