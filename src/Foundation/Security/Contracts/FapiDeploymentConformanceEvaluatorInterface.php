<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiDeploymentConformanceAssessment;
use Sif\Foundation\Security\Fapi\FapiDeploymentContext;
use Sif\Foundation\Security\Fapi\FapiDeploymentProfile;

interface FapiDeploymentConformanceEvaluatorInterface
{
    public function evaluate(
        FapiDeploymentProfile $profile,
        FapiDeploymentContext $context
    ): FapiDeploymentConformanceAssessment;
}
