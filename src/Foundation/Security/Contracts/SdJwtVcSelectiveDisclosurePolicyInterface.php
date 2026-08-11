<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcCredentialPayload;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcSelectiveDisclosureSet;

interface SdJwtVcSelectiveDisclosurePolicyInterface
{
    public function validate(
        SdJwtVcCredentialPayload $payload,
        SdJwtVcSelectiveDisclosureSet $disclosures
    ): void;
}
