<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlAuthenticatedIdentity
{
    /**
     * @param array<string, scalar|list<scalar>|null> $attributes
     */
    public function __construct(
        private string $subjectIdentifier,
        private SamlEntityId $issuer,
        private array $attributes = []
    ) {
    }

    public function subjectIdentifier(): string
    {
        return $this->subjectIdentifier;
    }

    public function issuer(): SamlEntityId
    {
        return $this->issuer;
    }

    /**
     * @return array<string, scalar|list<scalar>|null>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
