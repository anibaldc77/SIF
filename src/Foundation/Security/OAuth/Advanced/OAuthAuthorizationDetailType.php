<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Advanced;

use InvalidArgumentException;

final readonly class OAuthAuthorizationDetailType
{
    public function __construct(private string $value)
    {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException(
                'OAuth authorization detail type is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
