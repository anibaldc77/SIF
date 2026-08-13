<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\Runtime;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\OpenIdFederationRuntimeFailurePolicyInterface;

final readonly class DefaultOpenIdFederationRuntimeFailurePolicy implements OpenIdFederationRuntimeFailurePolicyInterface
{
    public function __construct(
        private bool $allowUsableStale = false
    ) {
    }

    public function mayUseCachedEvidence(
        OpenIdFederationRuntimeEvidence $evidence,
        DateTimeImmutable $at,
        \Throwable $failure
    ): bool {
        return $this->allowUsableStale
            && $evidence->freshnessAt($at)
                === OpenIdFederationRuntimeFreshnessStatus::StaleUsable;
    }
}
