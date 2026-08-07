<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\JwtSignatureVerifierInterface;
use Sif\Foundation\Security\Contracts\OidcIdTokenParserInterface;

final readonly class OidcIdTokenValidator
{
    public function __construct(
        private OidcIdTokenParserInterface $parser,
        private JwtSignatureVerifierInterface $signatureVerifier,
        private OidcIdTokenValidationPolicy $policy
    ) {
    }

    public function validate(
        OidcIdToken $idToken,
        OidcNonce $expectedNonce,
        DateTimeImmutable $now
    ): ?OidcIdTokenValidationResult {
        $jwt = $this->parser->parse($idToken);

        if (!in_array(
            $jwt->header()->algorithm(),
            $this->policy->allowedAlgorithms(),
            true
        )) {
            return null;
        }

        if (!$this->signatureVerifier->verify($jwt)) {
            return null;
        }

        $claims = $jwt->claims();

        if ($claims->issuer() !== $this->policy->issuer()) {
            return null;
        }

        if (!in_array(
            $this->policy->clientId(),
            $claims->audiences(),
            true
        )) {
            return null;
        }

        $expiresAt = $claims->expiresAt();

        if (
            $expiresAt === null
            || $expiresAt <= $now->sub($this->policy->clockSkew())
        ) {
            return null;
        }

        if (
            $claims->issuedAt() !== null
            && $claims->issuedAt() > $now->add($this->policy->clockSkew())
        ) {
            return null;
        }

        if (
            $claims->notBefore() !== null
            && $claims->notBefore() > $now->add($this->policy->clockSkew())
        ) {
            return null;
        }

        $nonce = $claims->additional()['nonce'] ?? null;

        if (
            !is_string($nonce)
            || !hash_equals(
                $expectedNonce->value(),
                $nonce
            )
        ) {
            return null;
        }

        return new OidcIdTokenValidationResult(
            new OidcFederatedIdentity(
                $this->policy->issuer(),
                $claims->subject(),
                $claims->additional()
            ),
            $idToken
        );
    }
}
