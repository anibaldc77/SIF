<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Haip\OpenId4VcHaipConformanceContext;
use Sif\Foundation\Security\VerifiableCredentials\HighAssuranceInteroperabilityContext;

interface OpenId4VcHaipInteroperabilityBridgeInterface
{
    public function map(
        OpenId4VcHaipConformanceContext $context
    ): HighAssuranceInteroperabilityContext;
}
