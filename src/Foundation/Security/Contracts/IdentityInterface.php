<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Identity\IdentityId;

interface IdentityInterface
{
    public function id(): IdentityId;
}
