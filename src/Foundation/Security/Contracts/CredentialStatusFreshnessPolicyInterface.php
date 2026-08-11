<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusAssessment;

interface CredentialStatusFreshnessPolicyInterface
{
    public function validate(
        CredentialStatusAssessment $assessment
    ): void;
}
