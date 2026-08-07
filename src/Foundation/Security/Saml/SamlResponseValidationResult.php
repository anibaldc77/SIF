<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlResponseValidationResult
{
    /** @param list<string> $violations */
    public function __construct(private array $violations)
    {
    }

    public static function success(): self
    {
        return new self([]);
    }

    /** @param list<string> $violations */
    public static function failed(array $violations): self
    {
        return new self($violations);
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
