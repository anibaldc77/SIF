<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

interface IdentityVerificationActivatorInterface
{
    public function markVerified(IdentityInterface $identity): void;
}
