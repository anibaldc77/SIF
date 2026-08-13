<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\Runtime;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\OpenIdFederationRuntimeFreshnessPolicyInterface;

final readonly class DefaultOpenIdFederationRuntimeFreshnessPolicy implements OpenIdFederationRuntimeFreshnessPolicyInterface
{
    public function evaluate(
        OpenIdFederationRuntimeEvidence $evidence,
        DateTimeImmutable $at
    ): OpenIdFederationRuntimeFreshnessStatus {
        return $evidence->freshnessAt($at);
    }
}
