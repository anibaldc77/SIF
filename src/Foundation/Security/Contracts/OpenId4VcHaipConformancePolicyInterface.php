<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Haip\OpenId4VcHaipConformanceAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Haip\OpenId4VcHaipConformanceContext;

interface OpenId4VcHaipConformancePolicyInterface
{
    public function assess(
        OpenId4VcHaipConformanceContext $context
    ): OpenId4VcHaipConformanceAssessment;
}
