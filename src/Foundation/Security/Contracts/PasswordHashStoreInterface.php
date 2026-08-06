<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Password\StoredPasswordHash;

interface PasswordHashStoreInterface extends PasswordHashProviderInterface
{
    public function replaceFor(IdentityInterface $identity, StoredPasswordHash $hash): void;
}
