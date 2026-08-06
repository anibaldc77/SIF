<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Password\PasswordSecret;
use Sif\Foundation\Security\Password\StoredPasswordHash;

interface PasswordHasherInterface
{
    public function hash(PasswordSecret $secret): StoredPasswordHash;
}
