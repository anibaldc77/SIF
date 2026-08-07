<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlStatusCode
{
    public const SUCCESS = 'urn:oasis:names:tc:SAML:2.0:status:Success';

    public function __construct(private string $value)
    {
    }

    public function value(): string
    {
        return $this->value;
    }

    public function successful(): bool
    {
        return $this->value === self::SUCCESS;
    }
}
