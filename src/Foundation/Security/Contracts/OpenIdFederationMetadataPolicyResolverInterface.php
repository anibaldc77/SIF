<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\MetadataPolicy\OpenIdFederationMetadataPolicy;
use Sif\Foundation\Security\OpenIdFederation\MetadataPolicy\OpenIdFederationResolvedMetadataPolicy;

interface OpenIdFederationMetadataPolicyResolverInterface
{
    /**
     * @param list<OpenIdFederationMetadataPolicy> $policies
     * @param list<string> $sourceEntityIds
     */
    public function resolve(
        array $policies,
        array $sourceEntityIds
    ): OpenIdFederationResolvedMetadataPolicy;
}
