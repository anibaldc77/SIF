<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use Sif\Foundation\Security\Contracts\PkceCodeChallengeFactoryInterface;

final readonly class NativeS256PkceCodeChallengeFactory implements PkceCodeChallengeFactoryInterface
{
    public function create(
        PkceCodeVerifier $verifier
    ): PkceCodeChallenge {
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

        return new PkceCodeChallenge($encoded);
    }
}
