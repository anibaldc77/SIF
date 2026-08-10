<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialIssuanceProof
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private string $proofType,
        private string $serializedProof,
        private array $attributes = []
    ) {
        if (
            trim($this->proofType) === ''
            || trim($this->serializedProof) === ''
        ) {
            throw new InvalidArgumentException(
                'Credential issuance proof is invalid.'
            );
        }
    }

    public function proofType(): string
    {
        return $this->proofType;
    }

    public function serializedProof(): string
    {
        return $this->serializedProof;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
