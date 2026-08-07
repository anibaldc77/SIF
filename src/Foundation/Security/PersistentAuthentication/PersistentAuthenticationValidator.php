<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

use Sif\Foundation\Security\Exceptions\InvalidPersistentAuthenticationCredentialException;

final readonly class PersistentAuthenticationValidator
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);

        if (
            strlen($normalized) < 32
            || strlen($normalized) > 256
            || preg_match('/^[A-Za-z0-9_-]+$/', $normalized) !== 1
        ) {
            throw new InvalidPersistentAuthenticationCredentialException(
                'Persistent authentication validator must be bounded and URL-safe.'
            );
        }

        $this->value = $normalized;
    }

    public function expose(callable $consumer): mixed
    {
        return $consumer($this->value);
    }

    public function digest(): PersistentAuthenticationValidatorDigest
    {
        return new PersistentAuthenticationValidatorDigest(
            hash('sha256', $this->value)
        );
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
            'Persistent authentication validators cannot be serialized.'
        );
    }

    public function __clone()
    {
        throw new \LogicException(
            'Persistent authentication validators cannot be cloned.'
        );
    }
}
