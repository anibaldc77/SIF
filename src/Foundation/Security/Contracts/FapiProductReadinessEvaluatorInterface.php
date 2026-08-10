<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiProductProfile;
use Sif\Foundation\Security\Fapi\FapiProductReadinessReport;

interface FapiProductReadinessEvaluatorInterface
{
    public function evaluate(
        FapiProductProfile $profile
    ): FapiProductReadinessReport;
}
