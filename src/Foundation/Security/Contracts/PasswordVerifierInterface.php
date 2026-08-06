<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Password\PasswordCredential;
use Sif\Foundation\Security\Password\PasswordVerificationResult;
use Sif\Foundation\Security\Password\StoredPasswordHash;

interface PasswordVerifierInterface
{
    public function verify(
        PasswordCredential $credential,
        StoredPasswordHash $storedHash
    ): PasswordVerificationResult;
}
