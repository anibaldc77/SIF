<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use InvalidArgumentException;

final readonly class OAuthRedirectUri
{
    public function __construct(private string $value)
    {
        if (
            filter_var($this->value, FILTER_VALIDATE_URL) === false
        ) {
            throw new InvalidArgumentException(
                'OAuth redirect URI is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
