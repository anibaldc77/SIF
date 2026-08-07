<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use InvalidArgumentException;

final readonly class OidcClientSecret
{
    public function __construct(private string $value)
    {
        if (
            strlen($this->value) < 16
            || strlen($this->value) > 4096
            || preg_match('/[\x00-\x1F\x7F]/', $this->value) === 1
        ) {
            throw new InvalidArgumentException(
                'OIDC client secret is invalid.'
            );
        }
    }

    public function expose(callable $consumer): mixed
    {
        return $consumer($this->value);
    }

    public function __toString(): string
    {
        return '[REDACTED]';
    }

    /** @return array{value:string} */
    public function __debugInfo(): array
    {
        return ['value' => '[REDACTED]'];
    }

    /** @return never */
    public function __serialize(): array
    {
        throw new \LogicException(
            'OIDC client secrets cannot be serialized.'
        );
    }
}
