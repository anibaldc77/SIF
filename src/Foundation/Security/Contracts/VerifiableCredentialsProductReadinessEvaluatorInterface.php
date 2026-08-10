<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredentialsProductProfile;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredentialsProductReadinessReport;

interface VerifiableCredentialsProductReadinessEvaluatorInterface
{
    public function evaluate(
        VerifiableCredentialsProductProfile $profile
    ): VerifiableCredentialsProductReadinessReport;
}
