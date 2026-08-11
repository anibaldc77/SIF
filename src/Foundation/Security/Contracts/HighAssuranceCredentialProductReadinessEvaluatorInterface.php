<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceCredentialProductProfile;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceCredentialProductReadinessReport;

interface HighAssuranceCredentialProductReadinessEvaluatorInterface
{
    public function evaluate(
        HighAssuranceCredentialProductProfile $profile
    ): HighAssuranceCredentialProductReadinessReport;
}
