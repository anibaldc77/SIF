<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SecurityEventInteroperabilityAssessment;
use Sif\Foundation\Security\SharedSignals\SecurityEventProvisioningContext;

interface SecurityEventProvisioningInteroperabilityPolicyInterface
{
    public function assess(
        SecurityEventProvisioningContext $context
    ): SecurityEventInteroperabilityAssessment;
}
