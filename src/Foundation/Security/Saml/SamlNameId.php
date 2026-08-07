<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use InvalidArgumentException;

final readonly class SamlNameId
{
    public function __construct(
        private string $value,
        private ?string $format = null
    ) {
        if ($this->value === '' || strlen($this->value) > 2048) {
            throw new InvalidArgumentException(
                'SAML NameID is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function format(): ?string
    {
        return $this->format;
    }
}
