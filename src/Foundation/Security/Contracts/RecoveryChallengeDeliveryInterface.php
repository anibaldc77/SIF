<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Recovery\RecoveryChallenge;
use Sif\Foundation\Security\Recovery\RecoveryToken;

interface RecoveryChallengeDeliveryInterface
{
    public function deliver(
        IdentityInterface $identity,
        RecoveryChallenge $challenge,
        RecoveryToken $token
    ): void;
}
