<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SecurityEventToken;

interface SecurityEventTokenAudienceValidatorInterface
{
    public function validate(
        SecurityEventToken $token,
        string $expectedAudience
    ): void;
}
