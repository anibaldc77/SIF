<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcKeyBindingContext;

interface SdJwtVcKeyBindingPolicyInterface
{
    public function validate(
        SdJwtVcKeyBindingContext $context
    ): void;
}
