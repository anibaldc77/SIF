<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\Interoperability\CredentialFormatInteroperabilityAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Formats\Interoperability\CredentialFormatPresentationProfile;

interface OpenId4VpCredentialFormatAdapterInterface
{
    public function assess(
        CredentialFormatPresentationProfile $profile
    ): CredentialFormatInteroperabilityAssessment;
}
