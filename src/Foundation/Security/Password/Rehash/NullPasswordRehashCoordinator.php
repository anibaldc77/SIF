<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Rehash;

use Sif\Foundation\Security\Contracts\IdentityInterface;
use Sif\Foundation\Security\Contracts\PasswordRehashCoordinatorInterface;
use Sif\Foundation\Security\Password\PasswordCredential;
use Sif\Foundation\Security\Password\StoredPasswordHash;

final readonly class NullPasswordRehashCoordinator implements PasswordRehashCoordinatorInterface
{
    public function rehash(
        IdentityInterface $identity,
        PasswordCredential $verifiedCredential,
        StoredPasswordHash $currentHash
    ): void {
    }
}
