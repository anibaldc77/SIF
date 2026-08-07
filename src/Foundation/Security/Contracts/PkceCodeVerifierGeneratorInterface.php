<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Oidc\PkceCodeVerifier;

interface PkceCodeVerifierGeneratorInterface
{
    public function generate(): PkceCodeVerifier;
}
