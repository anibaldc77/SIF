<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlXmlSignatureVerificationResult
{
    /** @param list<string> $violations */
    public function __construct(
        private bool $verified,
        private array $violations = []
    ) {
    }

    public static function success(): self
    {
        return new self(true);
    }

    /** @param list<string> $violations */
    public static function failed(array $violations): self
    {
        return new self(false, $violations);
    }

    public function verified(): bool
    {
        return $this->verified;
    }

    /** @return list<string> */
    public function violations(): array
    {
        return $this->violations;
    }
}
