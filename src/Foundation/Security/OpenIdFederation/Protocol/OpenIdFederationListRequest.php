<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\Protocol;

use InvalidArgumentException;

final readonly class OpenIdFederationListRequest
{
    public function __construct(
        private string $issuerEntityId,
        private ?string $entityType = null,
        private ?string $trustMarked = null
    ) {
        if (trim($this->issuerEntityId) === '') {
            throw new InvalidArgumentException(
                'OpenID Federation list request issuer is invalid.'
            );
        }

        if ($this->entityType !== null && trim($this->entityType) === '') {
            throw new InvalidArgumentException(
                'OpenID Federation list request entity type is invalid.'
            );
        }

        if ($this->trustMarked !== null && trim($this->trustMarked) === '') {
            throw new InvalidArgumentException(
                'OpenID Federation list request trust-mark filter is invalid.'
            );
        }
    }

    public function issuerEntityId(): string
    {
        return $this->issuerEntityId;
    }

    public function entityType(): ?string
    {
        return $this->entityType;
    }

    public function trustMarked(): ?string
    {
        return $this->trustMarked;
    }
}
