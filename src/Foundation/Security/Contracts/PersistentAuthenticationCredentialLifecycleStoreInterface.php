<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationCredential;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationSelector;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationValidatorDigest;

interface PersistentAuthenticationCredentialLifecycleStoreInterface extends PersistentAuthenticationCredentialStoreInterface
{
    public function rotate(
        PersistentAuthenticationSelector $selector,
        PersistentAuthenticationValidatorDigest $currentDigest,
        PersistentAuthenticationCredential $replacement
    ): bool;

    public function revoke(
        PersistentAuthenticationSelector $selector,
        DateTimeImmutable $revokedAt
    ): bool;
}
