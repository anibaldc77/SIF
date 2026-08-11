<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Status\Token\TokenStatusListValue;

interface TokenStatusListValuePolicyInterface
{
    public function validate(
        TokenStatusListValue $value
    ): void;
}
