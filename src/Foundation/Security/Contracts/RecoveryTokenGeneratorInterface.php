<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Recovery\RecoveryToken;

interface RecoveryTokenGeneratorInterface
{
    public function generate(): RecoveryToken;
}
