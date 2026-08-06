<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Password\StoredPasswordHash;

interface PasswordRehashRequiredHandlerInterface
{
    public function handle(IdentityInterface $identity, StoredPasswordHash $currentHash): void;
}
