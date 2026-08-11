<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpProductReadinessContext;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpProductReadinessReport;

interface OpenId4VpProductReadinessEvaluatorInterface
{
    public function evaluate(
        OpenId4VpProductReadinessContext $context
    ): OpenId4VpProductReadinessReport;
}
