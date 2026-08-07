<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\TrustedDevice;

use DateTimeImmutable;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Contracts\TrustedDevicePolicyInterface;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class DefaultTrustedDevicePolicy implements TrustedDevicePolicyInterface
{
    public function evaluate(
        AuthenticatedPrincipal $principal,
        TrustedDeviceGrant $grant,
        AuthenticationLevel $requiredLevel,
        DateTimeImmutable $now
    ): TrustedDevicePolicyDecision {
        if (
            $grant->identityId()->value()
            !== $principal->identity()->id()->value()
        ) {
            return TrustedDevicePolicyDecision::rejected(
                'Trusted-device grant belongs to another identity.'
            );
        }

        if (!$grant->isUsableAt($now)) {
            return TrustedDevicePolicyDecision::rejected(
                'Trusted-device grant is expired or revoked.'
            );
        }

        if ($principal->evidence()->level()->satisfies($requiredLevel)) {
            return TrustedDevicePolicyDecision::trustedWithoutMfaBypass(
                'Current authentication already satisfies the required level.'
            );
        }

        return TrustedDevicePolicyDecision::trustedWithoutMfaBypass(
            'Trusted device recognized; stronger authentication remains required.'
        );
    }
}
