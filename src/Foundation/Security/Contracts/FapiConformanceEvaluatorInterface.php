<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiConformanceReport;
use Sif\Foundation\Security\Fapi\FapiSecurityProfile;

interface FapiConformanceEvaluatorInterface
{
    public function evaluate(
        FapiSecurityProfile $profile
    ): FapiConformanceReport;
}
