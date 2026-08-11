<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusProfile;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusReference;

interface HighAssuranceCredentialStatusResolverInterface
{
    public function resolve(
        CredentialStatusReference $reference,
        CredentialStatusProfile $profile
    ): CredentialStatusAssessment;
}
