<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationToken;

interface PersistentAuthenticationTokenGeneratorInterface
{
    public function generate(): PersistentAuthenticationToken;
}
