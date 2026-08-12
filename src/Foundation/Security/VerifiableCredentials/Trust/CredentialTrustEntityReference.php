<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust;

use InvalidArgumentException;

final readonly class CredentialTrustEntityReference
{
    public function __construct(
        private string $entityId,
        private CredentialTrustEntityRole $role,
        private ?string $trustFrameworkId = null
    ) {
        if (trim($this->entityId) === '') {
            throw new InvalidArgumentException(
                'Credential trust entity reference is invalid.'
            );
        }
    }

    public function entityId(): string
    {
        return $this->entityId;
    }

    public function role(): CredentialTrustEntityRole
    {
        return $this->role;
    }

    public function trustFrameworkId(): ?string
    {
        return $this->trustFrameworkId;
    }
}
