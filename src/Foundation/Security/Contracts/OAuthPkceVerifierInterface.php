<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthPkceChallenge;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthPkceVerifier;

interface OAuthPkceVerifierInterface
{
    public function verify(
        OAuthPkceVerifier $verifier,
        OAuthPkceChallenge $challenge
    ): bool;
}
