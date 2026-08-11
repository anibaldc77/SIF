<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcKeyBindingAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcKeyBindingContext;

interface SdJwtVcKeyBindingVerifierInterface
{
    public function verify(
        SdJwtVcKeyBindingContext $context
    ): SdJwtVcKeyBindingAssessment;
}
