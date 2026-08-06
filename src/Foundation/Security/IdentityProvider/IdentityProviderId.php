<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\IdentityProvider;

use Sif\Foundation\Security\Exceptions\InvalidIdentityProviderException;

final readonly class IdentityProviderId
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '' || preg_match('/^[a-z][a-z0-9._-]{0,63}$/', $normalized) !== 1) {
            throw new InvalidIdentityProviderException(
                'Identity provider identifier must be a stable lowercase identifier.'
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
        return $this->value === $other->value;
    }
}
