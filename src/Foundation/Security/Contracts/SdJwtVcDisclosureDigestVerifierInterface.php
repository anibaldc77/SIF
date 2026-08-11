<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcDisclosure;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcDisclosureReference;

interface SdJwtVcDisclosureDigestVerifierInterface
{
    public function matches(
        SdJwtVcDisclosure $disclosure,
        SdJwtVcDisclosureReference $reference
    ): bool;
}
