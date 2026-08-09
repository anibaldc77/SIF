<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

use Sif\Foundation\Security\Contracts\OAuthPkceVerifierInterface;

final readonly class DefaultOAuthPkceVerifier implements OAuthPkceVerifierInterface
{
    public function verify(
        OAuthPkceVerifier $verifier,
        OAuthPkceChallenge $challenge
    ): bool {
        if ($challenge->method()->value() !== OAuthPkceMethod::S256) {
            return false;
        }

        $digest = hash(
            'sha256',
            $verifier->value(),
            true
        );

        $encoded = rtrim(
            strtr(
                base64_encode($digest),
                '+/',
                '-_'
            ),
            '='
        );

        return hash_equals(
            $challenge->value(),
            $encoded
        );
    }
}
