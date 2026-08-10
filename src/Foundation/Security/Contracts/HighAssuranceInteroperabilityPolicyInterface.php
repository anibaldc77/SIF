<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\HighAssuranceInteroperabilityAssessment;
use Sif\Foundation\Security\VerifiableCredentials\HighAssuranceInteroperabilityContext;

interface HighAssuranceInteroperabilityPolicyInterface
{
    public function assess(
        HighAssuranceInteroperabilityContext $context
    ): HighAssuranceInteroperabilityAssessment;
}
