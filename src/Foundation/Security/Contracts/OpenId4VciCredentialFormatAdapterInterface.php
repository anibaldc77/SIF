<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\Interoperability\CredentialFormatInteroperabilityAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Formats\Interoperability\CredentialFormatIssuanceProfile;

interface OpenId4VciCredentialFormatAdapterInterface
{
    public function assess(
        CredentialFormatIssuanceProfile $profile
    ): CredentialFormatInteroperabilityAssessment;
}
