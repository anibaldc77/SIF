<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\OpenIdFederation\Runtime\OpenIdFederationRuntimeEvidence;

interface OpenIdFederationRuntimeFailurePolicyInterface
{
    public function mayUseCachedEvidence(
        OpenIdFederationRuntimeEvidence $evidence,
        DateTimeImmutable $at,
        \Throwable $failure
    ): bool;
}
