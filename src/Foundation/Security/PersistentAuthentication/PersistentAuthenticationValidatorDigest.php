<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

use Sif\Foundation\Security\Exceptions\InvalidPersistentAuthenticationCredentialException;

final readonly class PersistentAuthenticationValidatorDigest
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (preg_match('/^[a-f0-9]{64}$/', $normalized) !== 1) {
            throw new InvalidPersistentAuthenticationCredentialException(
                'Persistent authentication validator digest must be a SHA-256 hexadecimal value.'
            );
        }

        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->value, $other->value);
    }
}
