<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Registry;

use DateTimeImmutable;
use InvalidArgumentException;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityReference;

final readonly class CredentialTrustRegistryEntry
{
    /**
     * @param list<string> $accreditationIds
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $registryId,
        private CredentialTrustEntityReference $entity,
        private CredentialTrustRegistryMembershipStatus $membershipStatus,
        private DateTimeImmutable $validFrom,
        private ?DateTimeImmutable $validUntil = null,
        private array $accreditationIds = [],
        private array $metadata = []
    ) {
        if (trim($this->registryId) === '') {
            throw new InvalidArgumentException('Credential trust registry entry is invalid.');
        }

        if ($this->validUntil !== null && $this->validUntil < $this->validFrom) {
            throw new InvalidArgumentException('Credential trust registry validity interval is invalid.');
        }
    }

    public function registryId(): string
    {
        return $this->registryId;
    }

    public function entity(): CredentialTrustEntityReference
    {
        return $this->entity;
    }

    public function membershipStatus(): CredentialTrustRegistryMembershipStatus
    {
        return $this->membershipStatus;
    }

    public function validFrom(): DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function validUntil(): ?DateTimeImmutable
    {
        return $this->validUntil;
    }

    /** @return list<string> */
    public function accreditationIds(): array
    {
        return $this->accreditationIds;
    }

    /** @return array<string, mixed> */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
