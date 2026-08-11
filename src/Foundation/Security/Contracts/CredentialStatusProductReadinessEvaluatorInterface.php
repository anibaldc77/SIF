<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusProductProfile;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusProductReadinessReport;

interface CredentialStatusProductReadinessEvaluatorInterface
{
    public function evaluate(
        CredentialStatusProductProfile $profile
    ): CredentialStatusProductReadinessReport;
}