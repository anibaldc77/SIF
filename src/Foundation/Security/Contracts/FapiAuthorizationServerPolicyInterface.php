<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiAuthorizationServerSecurityAssessment;
use Sif\Foundation\Security\Fapi\FapiAuthorizationServerSecurityRequirements;
use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadata;

interface FapiAuthorizationServerPolicyInterface
{
    public function validateConfiguration(): void;

    public function assess(
        OAuthAuthorizationServerMetadata $metadata,
        FapiAuthorizationServerSecurityRequirements $requirements
    ): FapiAuthorizationServerSecurityAssessment;
}
