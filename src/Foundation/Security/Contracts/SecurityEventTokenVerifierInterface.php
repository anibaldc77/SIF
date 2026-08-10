<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SecurityEventToken;
use Sif\Foundation\Security\SharedSignals\SecurityEventTokenValidationContext;
use Sif\Foundation\Security\SharedSignals\SecurityEventTokenValidationResult;

interface SecurityEventTokenVerifierInterface
{
    public function verify(
        string $serializedToken,
        string $expectedIssuer,
        string $expectedAudience
    ): SecurityEventToken;

    public function verifyWithContext(
        string $serializedToken,
        SecurityEventTokenValidationContext $context
    ): SecurityEventTokenValidationResult;
}
