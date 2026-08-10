<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiSenderConstraintAssessment;
use Sif\Foundation\Security\Fapi\FapiSenderConstraintContext;
use Sif\Foundation\Security\Fapi\FapiSenderConstraintRequirements;

interface FapiSenderConstraintPolicyInterface
{
    public function assess(
        FapiSenderConstraintContext $context,
        FapiSenderConstraintRequirements $requirements
    ): FapiSenderConstraintAssessment;
}
