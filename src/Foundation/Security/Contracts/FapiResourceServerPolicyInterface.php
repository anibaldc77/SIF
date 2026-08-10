<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiResourceServerAssessment;
use Sif\Foundation\Security\Fapi\FapiResourceServerRequestContext;
use Sif\Foundation\Security\Fapi\FapiResourceServerSecurityRequirements;

interface FapiResourceServerPolicyInterface
{
    public function validateConfiguration(): void;

    public function assess(
        FapiResourceServerRequestContext $context,
        FapiResourceServerSecurityRequirements $requirements
    ): FapiResourceServerAssessment;
}
