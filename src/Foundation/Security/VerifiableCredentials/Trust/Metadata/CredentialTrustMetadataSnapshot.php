<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Metadata;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class CredentialTrustMetadataSnapshot
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private string $entityId,
        private string $version,
        private string $fingerprint,
        private DateTimeImmutable $retrievedAt,
        private ?DateTimeImmutable $expiresAt = null,
        private array $attributes = []
    ) {
        if (
            trim($this->entityId) === ''
            || trim($this->version) === ''
            || trim($this->fingerprint) === ''
        ) {
            throw new InvalidArgumentException(
                'Credential trust metadata snapshot is invalid.'
            );
        }

        if (
            $this->expiresAt !== null
            && $this->expiresAt < $this->retrievedAt
        ) {
            throw new InvalidArgumentException(
                'Credential trust metadata snapshot validity is invalid.'
            );
        }
    }

    public function entityId(): string
    {
        return $this->entityId;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function fingerprint(): string
    {
        return $this->fingerprint;
    }

    public function retrievedAt(): DateTimeImmutable
    {
        return $this->retrievedAt;
    }

    public function expiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /** @return array<string, mixed> */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
