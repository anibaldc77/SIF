<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\MetadataPolicy\OpenIdFederationMetadataPolicy;
use Sif\Foundation\Security\OpenIdFederation\MetadataPolicy\OpenIdFederationMetadataPolicyApplicationResult;

interface OpenIdFederationMetadataPolicyApplicatorInterface
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function apply(
        array $metadata,
        string $entityType,
        OpenIdFederationMetadataPolicy $policy
    ): OpenIdFederationMetadataPolicyApplicationResult;
}
