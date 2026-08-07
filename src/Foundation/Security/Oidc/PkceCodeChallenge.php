<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use InvalidArgumentException;

final readonly class PkceCodeChallenge
{
    public function __construct(private string $value)
    {
        if (
            $this->value === ''
            || strlen($this->value) > 128
            || preg_match('/^[A-Za-z0-9_-]+$/', $this->value) !== 1
        ) {
            throw new InvalidArgumentException(
                'PKCE code challenge must be URL-safe.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
