<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationCredential;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationSelector;

interface PersistentAuthenticationCredentialStoreInterface
{
    public function save(PersistentAuthenticationCredential $credential): void;

    public function findBySelector(
        PersistentAuthenticationSelector $selector
    ): ?PersistentAuthenticationCredential;
}
