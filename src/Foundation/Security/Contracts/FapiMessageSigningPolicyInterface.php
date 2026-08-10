<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiMessageSigningAssessment;
use Sif\Foundation\Security\Fapi\FapiMessageSigningRequirements;

interface FapiMessageSigningPolicyInterface
{
    public function assess(
        FapiMessageSigningRequirements $requirements
    ): FapiMessageSigningAssessment;
}
