<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Password\PasswordCredential;
use Sif\Foundation\Security\Password\StoredPasswordHash;

interface PasswordRehashCoordinatorInterface
{
    public function rehash(
        IdentityInterface $identity,
        PasswordCredential $verifiedCredential,
        StoredPasswordHash $currentHash
    ): void;
}
