<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\MultiFactor\Totp\TotpFactorId;
use Sif\Foundation\Security\MultiFactor\Totp\TotpFactorRecord;

interface TotpFactorStoreInterface
{
    public function save(TotpFactorRecord $factor): void;

    public function find(TotpFactorId $id): ?TotpFactorRecord;

    public function findActiveForIdentity(IdentityId $identityId): ?TotpFactorRecord;

    public function activate(TotpFactorId $id, int $acceptedCounter): bool;

    public function acceptCounter(TotpFactorId $id, int $acceptedCounter): bool;

    public function revoke(TotpFactorId $id): bool;
}
