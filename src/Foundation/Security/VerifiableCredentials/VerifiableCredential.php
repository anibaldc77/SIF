<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class VerifiableCredential
{
    /**
     * @param list<string> $types
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $issuer,
        private VerifiableCredentialSubject $subject,
        private array $types,
        private ?DateTimeImmutable $validFrom = null,
        private ?DateTimeImmutable $validUntil = null,
        private array $metadata = []
    ) {
        if (trim($this->issuer) === '' || $this->types === []) {
            throw new InvalidArgumentException(
                'Verifiable credential is invalid.'
            );
        }
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function subject(): VerifiableCredentialSubject
    {
        return $this->subject;
    }

    /**
     * @return list<string>
     */
    public function types(): array
    {
        return $this->types;
    }

    public function validFrom(): ?DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function validUntil(): ?DateTimeImmutable
    {
        return $this->validUntil;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
