<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\TrustedDevice;

use DateTimeImmutable;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Contracts\TrustedDeviceGrantStoreInterface;
use Sif\Foundation\Security\Contracts\TrustedDevicePolicyInterface;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class TrustedDevicePolicyService
{
    public function __construct(
        private TrustedDeviceGrantStoreInterface $store,
        private TrustedDevicePolicyInterface $policy
    ) {
    }

    public function evaluate(
        AuthenticatedPrincipal $principal,
        TrustedDeviceGrantId $grantId,
        AuthenticationLevel $requiredLevel,
        DateTimeImmutable $now
    ): TrustedDevicePolicyDecision {
        $grant = $this->store->find($grantId);

        if ($grant === null) {
            return TrustedDevicePolicyDecision::rejected(
                'Trusted-device grant was not found.'
            );
        }

        return $this->policy->evaluate(
            $principal,
            $grant,
            $requiredLevel,
            $now
        );
    }
}
