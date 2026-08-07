<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengeId;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengePurpose;

interface MultiFactorChallengeLifecycleStoreInterface extends MultiFactorChallengeStoreInterface
{
    public function satisfy(MultiFactorChallengeId $id, DateTimeImmutable $satisfiedAt): bool;

    public function revoke(MultiFactorChallengeId $id): bool;

    public function revokePendingForIdentity(
        IdentityId $identityId,
        MultiFactorChallengePurpose $purpose
    ): int;
}
