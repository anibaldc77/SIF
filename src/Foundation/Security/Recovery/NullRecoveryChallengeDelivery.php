<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery;

use Sif\Foundation\Security\Contracts\IdentityInterface;
use Sif\Foundation\Security\Contracts\RecoveryChallengeDeliveryInterface;

final readonly class NullRecoveryChallengeDelivery implements RecoveryChallengeDeliveryInterface
{
    public function deliver(
        IdentityInterface $identity,
        RecoveryChallenge $challenge,
        RecoveryToken $token
    ): void {
    }
}
