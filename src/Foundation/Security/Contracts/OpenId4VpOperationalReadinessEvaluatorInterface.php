<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpOperationalReadinessContext;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpOperationalReadinessReport;

interface OpenId4VpOperationalReadinessEvaluatorInterface
{
    public function evaluate(
        OpenId4VpOperationalReadinessContext $context
    ): OpenId4VpOperationalReadinessReport;
}
