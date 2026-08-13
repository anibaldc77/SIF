<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\Protocol;

use InvalidArgumentException;

final readonly class OpenIdFederationResolveRequest
{
    /**
     * @param list<string> $trustAnchorEntityIds
     * @param list<string> $entityTypes
     */
    public function __construct(
        private string $subjectEntityId,
        private array $trustAnchorEntityIds,
        private array $entityTypes = []
    ) {
        if (
            trim($this->subjectEntityId) === ''
            || $this->trustAnchorEntityIds === []
        ) {
            throw new InvalidArgumentException(
                'OpenID Federation resolve request is invalid.'
            );
        }

        foreach ($this->trustAnchorEntityIds as $trustAnchorEntityId) {
            if (trim($trustAnchorEntityId) === '') {
                throw new InvalidArgumentException(
                    'OpenID Federation resolve request trust anchor is invalid.'
                );
            }
        }

        foreach ($this->entityTypes as $entityType) {
            if (trim($entityType) === '') {
                throw new InvalidArgumentException(
                    'OpenID Federation resolve request entity type is invalid.'
                );
            }
        }
    }

    public function subjectEntityId(): string
    {
        return $this->subjectEntityId;
    }

    /** @return list<string> */
    public function trustAnchorEntityIds(): array
    {
        return $this->trustAnchorEntityIds;
    }

    /** @return list<string> */
    public function entityTypes(): array
    {
        return $this->entityTypes;
    }
}
