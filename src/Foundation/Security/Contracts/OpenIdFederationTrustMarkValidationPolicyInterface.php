<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\TrustMarks\OpenIdFederationTrustMark;
use Sif\Foundation\Security\OpenIdFederation\TrustMarks\OpenIdFederationTrustMarkValidationContext;
use Sif\Foundation\Security\OpenIdFederation\TrustMarks\OpenIdFederationTrustMarkValidationResult;

interface OpenIdFederationTrustMarkValidationPolicyInterface
{
    public function validate(
        OpenIdFederationTrustMark $trustMark,
        OpenIdFederationTrustMarkValidationContext $context
    ): OpenIdFederationTrustMarkValidationResult;
}
