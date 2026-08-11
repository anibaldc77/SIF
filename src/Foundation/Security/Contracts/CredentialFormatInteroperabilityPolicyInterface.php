<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\Interoperability\CredentialFormatIssuanceProfile;
use Sif\Foundation\Security\VerifiableCredentials\Formats\Interoperability\CredentialFormatPresentationProfile;

interface CredentialFormatInteroperabilityPolicyInterface
{
    public function validateIssuance(
        CredentialFormatIssuanceProfile $profile
    ): void;

    public function validatePresentation(
        CredentialFormatPresentationProfile $profile
    ): void;
}
