<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\TrustedDevice\TrustedDeviceGrant;
use Sif\Foundation\Security\TrustedDevice\TrustedDeviceGrantId;

interface TrustedDeviceGrantStoreInterface
{
    public function save(TrustedDeviceGrant $grant): void;

    public function find(TrustedDeviceGrantId $id): ?TrustedDeviceGrant;

    /** @return list<TrustedDeviceGrant> */
    public function findActiveForIdentity(IdentityId $identityId): array;
}
