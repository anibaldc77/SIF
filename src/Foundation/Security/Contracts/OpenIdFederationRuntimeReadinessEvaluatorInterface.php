<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\Runtime\OpenIdFederationInteroperabilityProfile;
use Sif\Foundation\Security\OpenIdFederation\Runtime\OpenIdFederationRuntimeReadinessReport;

interface OpenIdFederationRuntimeReadinessEvaluatorInterface
{
    public function evaluate(
        OpenIdFederationInteroperabilityProfile $profile
    ): OpenIdFederationRuntimeReadinessReport;
}
