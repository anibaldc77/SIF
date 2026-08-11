<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc;

use InvalidArgumentException;

final readonly class SdJwtVcIssuerIdentity
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private string $identifier,
        private array $attributes = []
    ) {
        if (trim($this->identifier) === '') {
            throw new InvalidArgumentException(
                'SD-JWT VC issuer identity is invalid.'
            );
        }
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
