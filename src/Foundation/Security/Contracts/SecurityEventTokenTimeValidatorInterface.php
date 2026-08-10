<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SecurityEventToken;
use Sif\Foundation\Security\SharedSignals\SecurityEventTokenValidationContext;

interface SecurityEventTokenTimeValidatorInterface
{
    public function validate(
        SecurityEventToken $token,
        SecurityEventTokenValidationContext $context
    ): void;
}
