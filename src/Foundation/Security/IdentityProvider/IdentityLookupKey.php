<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\IdentityProvider;

use Sif\Foundation\Security\Exceptions\InvalidIdentityLookupException;

final readonly class IdentityLookupKey
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);

        if ($normalized === '' || strlen($normalized) > 255 || preg_match('/[\x00-\x1F\x7F]/', $normalized) === 1) {
            throw new InvalidIdentityLookupException(
                'Identity lookup key must be non-empty, bounded and free of control characters.'
            );
        }

        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }
}
