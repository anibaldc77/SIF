<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\RecoveryCodeDigest;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\RecoveryCodeRecord;

interface RecoveryCodeStoreInterface
{
    /** @param list<RecoveryCodeRecord> $records */
    public function replaceForIdentity(IdentityId $identityId, array $records): void;

    public function hasAvailableForIdentity(IdentityId $identityId): bool;

    public function consume(
        IdentityId $identityId,
        RecoveryCodeDigest $digest,
        DateTimeImmutable $consumedAt
    ): bool;
}
