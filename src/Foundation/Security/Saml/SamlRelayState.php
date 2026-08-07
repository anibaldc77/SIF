<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use InvalidArgumentException;

final readonly class SamlRelayState
{
    public function __construct(private string $value)
    {
        if (
            $this->value === ''
            || strlen($this->value) > 80
        ) {
            throw new InvalidArgumentException(
                'SAML RelayState must contain between 1 and 80 bytes.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
