<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

use InvalidArgumentException;

final readonly class OAuthIssuerIdentifier
{
    public function __construct(private string $value)
    {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException(
                'OAuth issuer identifier is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function matches(OAuthIssuerIdentifier $other): bool
    {
        return hash_equals($this->value, $other->value());
    }
}
