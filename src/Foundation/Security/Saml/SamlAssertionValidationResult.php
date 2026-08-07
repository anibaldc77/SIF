<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlAssertionValidationResult
{
    /** @param list<string> $violations */
    public function __construct(private array $violations)
    {
    }

    public function valid(): bool
    {
        return $this->violations === [];
    }

    /** @return list<string> */
    public function violations(): array
    {
        return $this->violations;
    }
}
