<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use InvalidArgumentException;

final readonly class OidcAuthorizationCode
{
    public function __construct(private string $value)
    {
        if (
            $this->value === ''
            || strlen($this->value) > 4096
            || preg_match('/[\x00-\x20\x7F]/', $this->value) === 1
        ) {
            throw new InvalidArgumentException(
                'OIDC authorization code is invalid.'
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
            'OIDC authorization codes cannot be serialized.'
        );
    }
}
