<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use InvalidArgumentException;

final readonly class OAuthPkceMethod
{
    public const S256 = 'S256';

    public function __construct(private string $value)
    {
        if ($this->value !== self::S256) {
            throw new InvalidArgumentException(
                'OAuth PKCE method is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
