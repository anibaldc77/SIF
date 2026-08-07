<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Oidc\PkceCodeChallenge;
use Sif\Foundation\Security\Oidc\PkceCodeVerifier;

interface PkceCodeChallengeFactoryInterface
{
    public function create(
        PkceCodeVerifier $verifier
    ): PkceCodeChallenge;
}
