<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\TrustedDevice\TrustedDeviceGrantId;

interface TrustedDeviceGrantLifecycleStoreInterface extends TrustedDeviceGrantStoreInterface
{
    public function revoke(
        TrustedDeviceGrantId $id,
        DateTimeImmutable $revokedAt
    ): bool;

    public function revokeAllForIdentity(
        IdentityId $identityId,
        DateTimeImmutable $revokedAt
    ): int;
}
