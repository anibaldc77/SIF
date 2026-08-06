<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Credentials\CredentialType;

interface CredentialInterface
{
    public function type(): CredentialType;
}
