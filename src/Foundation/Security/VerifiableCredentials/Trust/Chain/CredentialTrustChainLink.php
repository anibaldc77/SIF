<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Chain;

use InvalidArgumentException;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityReference;

final readonly class CredentialTrustChainLink
{
    /**
     * @param list<string> $accreditationIds
     * @param list<string> $keyMaterialIds
     */
    public function __construct(
        private CredentialTrustEntityReference $entity,
        private ?string $parentEntityId,
        private array $accreditationIds = [],
        private array $keyMaterialIds = []
    ) {
        if ($this->parentEntityId !== null && trim($this->parentEntityId) === '') {
            throw new InvalidArgumentException(
                'Credential trust chain parent entity id is invalid.'
            );
        }
    }

    public function entity(): CredentialTrustEntityReference
    {
        return $this->entity;
    }

    public function parentEntityId(): ?string
    {
        return $this->parentEntityId;
    }

    /** @return list<string> */
    public function accreditationIds(): array
    {
        return $this->accreditationIds;
    }

    /** @return list<string> */
    public function keyMaterialIds(): array
    {
        return $this->keyMaterialIds;
    }
}
