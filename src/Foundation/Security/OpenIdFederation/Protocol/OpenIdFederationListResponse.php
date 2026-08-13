<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\Protocol;

final readonly class OpenIdFederationListResponse
{
    /** @param list<string> $entityIds */
    public function __construct(
        private array $entityIds
    ) {
    }

    /** @return list<string> */
    public function entityIds(): array
    {
        return $this->entityIds;
    }
}
