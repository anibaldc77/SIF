<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationReason;

interface FederatedSessionRevokerInterface
{
    public function revokeAll(
        IdentityId $identityId,
        FederatedRevocationReason $reason
    ): void;
}
