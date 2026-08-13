<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\MetadataPolicy;

final readonly class OpenIdFederationResolvedMetadataPolicy
{
    /**
     * @param list<string> $sourceEntityIds
     */
    public function __construct(
        private OpenIdFederationMetadataPolicy $policy,
        private array $sourceEntityIds
    ) {
    }

    public function policy(): OpenIdFederationMetadataPolicy
    {
        return $this->policy;
    }

    /** @return list<string> */
    public function sourceEntityIds(): array
    {
        return $this->sourceEntityIds;
    }
}
