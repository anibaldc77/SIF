<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use InvalidArgumentException;

final readonly class SamlAssertionId
{
    public function __construct(private string $value)
    {
        if (
            strlen($this->value) < 2
            || strlen($this->value) > 160
            || preg_match('/^_[A-Za-z0-9._:-]+$/', $this->value) !== 1
        ) {
            throw new InvalidArgumentException(
                'SAML assertion id is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
