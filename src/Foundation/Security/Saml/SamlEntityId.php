<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use InvalidArgumentException;

final readonly class SamlEntityId
{
    public function __construct(private string $value)
    {
        if (
            $this->value === ''
            || strlen($this->value) > 2048
        ) {
            throw new InvalidArgumentException(
                'SAML entity id is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
