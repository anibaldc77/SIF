<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Password\Authentication\PasswordHashProviderResult;

interface PasswordHashProviderInterface
{
    public function findFor(IdentityInterface $identity): PasswordHashProviderResult;
}
