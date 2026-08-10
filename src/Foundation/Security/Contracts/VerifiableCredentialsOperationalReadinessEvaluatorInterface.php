<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\HighAssuranceInteroperabilityAssessment;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredentialsOperationalReadinessReport;

interface VerifiableCredentialsOperationalReadinessEvaluatorInterface
{
    public function evaluate(
        HighAssuranceInteroperabilityAssessment $assessment
    ): VerifiableCredentialsOperationalReadinessReport;
}
