<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\TrustedDevice\TrustedDeviceGrant;
use Sif\Foundation\Security\TrustedDevice\TrustedDevicePolicyDecision;

interface TrustedDevicePolicyInterface
{
    public function evaluate(
        AuthenticatedPrincipal $principal,
        TrustedDeviceGrant $grant,
        AuthenticationLevel $requiredLevel,
        DateTimeImmutable $now
    ): TrustedDevicePolicyDecision;
}
