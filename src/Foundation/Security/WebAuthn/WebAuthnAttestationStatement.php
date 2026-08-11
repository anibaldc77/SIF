<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use InvalidArgumentException;

final readonly class WebAuthnAttestationStatement
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private string $format,
        private string $serializedStatement,
        private array $attributes = []
    ) {
        if (
            trim($this->format) === ''
            || trim($this->serializedStatement) === ''
        ) {
            throw new InvalidArgumentException(
                'WebAuthn attestation statement is invalid.'
            );
        }
    }

    public function format(): string
    {
        return $this->format;
    }

    public function serializedStatement(): string
    {
        return $this->serializedStatement;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
