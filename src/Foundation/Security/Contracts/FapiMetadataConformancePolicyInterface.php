<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiMetadataConformanceAssessment;
use Sif\Foundation\Security\Fapi\FapiMetadataConformanceRequirements;
use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadata;

interface FapiMetadataConformancePolicyInterface
{
    public function assess(
        OAuthAuthorizationServerMetadata $metadata,
        string $authoritativeIssuer,
        FapiMetadataConformanceRequirements $requirements
    ): FapiMetadataConformanceAssessment;
}
