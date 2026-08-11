<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceOperationalReadinessContext;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceOperationalReadinessReport;

interface HighAssuranceOperationalReadinessEvaluatorInterface
{
    public function evaluate(
        HighAssuranceOperationalReadinessContext $context
    ): HighAssuranceOperationalReadinessReport;
}
