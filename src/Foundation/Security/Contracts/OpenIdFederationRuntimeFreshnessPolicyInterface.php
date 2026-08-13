<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\OpenIdFederation\Runtime\OpenIdFederationRuntimeEvidence;
use Sif\Foundation\Security\OpenIdFederation\Runtime\OpenIdFederationRuntimeFreshnessStatus;

interface OpenIdFederationRuntimeFreshnessPolicyInterface
{
    public function evaluate(
        OpenIdFederationRuntimeEvidence $evidence,
        DateTimeImmutable $at
    ): OpenIdFederationRuntimeFreshnessStatus;
}
