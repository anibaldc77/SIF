<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Rehash;

use Sif\Foundation\Security\Contracts\IdentityInterface;
use Sif\Foundation\Security\Contracts\PasswordHasherInterface;
use Sif\Foundation\Security\Contracts\PasswordHashStoreInterface;
use Sif\Foundation\Security\Contracts\PasswordRehashCoordinatorInterface;
use Sif\Foundation\Security\Password\PasswordCredential;
use Sif\Foundation\Security\Password\StoredPasswordHash;

final readonly class PasswordRehashCoordinator implements PasswordRehashCoordinatorInterface
{
    public function __construct(
        private PasswordHasherInterface $hasher,
        private PasswordHashStoreInterface $store
    ) {
    }

    public function rehash(
        IdentityInterface $identity,
        PasswordCredential $verifiedCredential,
        StoredPasswordHash $currentHash
    ): void {
        $replacement = $this->hasher->hash($verifiedCredential->secret());
        $this->store->replaceFor($identity, $replacement);
    }
}
