<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use InvalidArgumentException;

final readonly class OidcIdToken
{
    public function __construct(private string $value)
    {
        if (
            strlen($this->value) < 16
            || strlen($this->value) > 16384
            || preg_match('/^[\x21-\x7E]+$/', $this->value) !== 1
        ) {
            throw new InvalidArgumentException(
                'OIDC ID Token must be bounded and contain visible ASCII characters only.'
            );
        }
    }

    public function expose(callable $consumer): mixed
    {
        return $consumer($this->value);
    }

    public function fingerprint(): string
    {
        return hash('sha256', $this->value);
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
            'OIDC ID Tokens cannot be serialized.'
        );
    }
}
