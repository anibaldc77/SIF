<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiDeploymentConformanceAssessment;
use Sif\Foundation\Security\Fapi\FapiDeploymentReadinessReport;

interface FapiDeploymentReadinessEvaluatorInterface
{
    public function evaluate(
        FapiDeploymentConformanceAssessment $assessment
    ): FapiDeploymentReadinessReport;
}
