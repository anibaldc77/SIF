<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use InvalidArgumentException;

final readonly class SamlEndpoint
{
    public function __construct(
        private string $location,
        private ?string $binding = null
    ) {
        if (
            $this->location === ''
            || strlen($this->location) > 4096
        ) {
            throw new InvalidArgumentException(
                'SAML endpoint location is invalid.'
            );
        }

        if ($this->binding !== null && strlen($this->binding) > 512) {
            throw new InvalidArgumentException(
                'SAML binding identifier is invalid.'
            );
        }
    }

    public function location(): string
    {
        return $this->location;
    }

    public function binding(): ?string
    {
        return $this->binding;
    }
}
