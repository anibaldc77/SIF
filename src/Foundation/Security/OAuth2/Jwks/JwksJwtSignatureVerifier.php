<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Jwks;

use Sif\Foundation\Security\Contracts\JwkSignatureVerifierInterface;
use Sif\Foundation\Security\Contracts\JwtSignatureVerifierInterface;
use Sif\Foundation\Security\OAuth2\Jwt\ParsedJwt;

final readonly class JwksJwtSignatureVerifier implements JwtSignatureVerifierInterface
{
    public function __construct(
        private JwkResolver $resolver,
        private JwkSignatureVerifierInterface $verifier
    ) {
    }

    public function verify(ParsedJwt $jwt): bool
    {
        $keyId = $jwt->header()->keyId();

        if ($keyId === null || $keyId === '') {
            return false;
        }

        $key = $this->resolver->resolve($keyId);

        if ($key === null) {
            return false;
        }

        if (
            $key->algorithm() !== null
            && $key->algorithm() !== $jwt->header()->algorithm()
        ) {
            return false;
        }

        return $this->verifier->verify($jwt, $key);
    }
}
