<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Authentication;

use Sif\Foundation\Security\Contracts\IdentityInterface;
use Sif\Foundation\Security\Contracts\PasswordRehashRequiredHandlerInterface;
use Sif\Foundation\Security\Password\StoredPasswordHash;

final readonly class NullPasswordRehashRequiredHandler implements PasswordRehashRequiredHandlerInterface
{
    public function handle(IdentityInterface $identity, StoredPasswordHash $currentHash): void
    {
    }
}
