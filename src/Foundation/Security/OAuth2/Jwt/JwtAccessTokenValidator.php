<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Jwt;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\AccessTokenValidatorInterface;
use Sif\Foundation\Security\Contracts\JwtParserInterface;
use Sif\Foundation\Security\Contracts\JwtSignatureVerifierInterface;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\OAuth2\AccessToken;
use Sif\Foundation\Security\OAuth2\ScopeSet;
use Sif\Foundation\Security\OAuth2\ValidatedAccessToken;

final readonly class JwtAccessTokenValidator implements AccessTokenValidatorInterface
{
    public function __construct(
        private JwtParserInterface $parser,
        private JwtSignatureVerifierInterface $signatureVerifier,
        private JwtValidationPolicy $policy
    ) {
    }

    public function validate(
        AccessToken $token,
        DateTimeImmutable $now
    ): ?ValidatedAccessToken {
        $jwt = $this->parser->parse($token);

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
        $skewedFuture = $now->add($this->policy->clockSkew());
        $skewedPast = $now->sub($this->policy->clockSkew());

        if (
            $claims->expiresAt() === null
            || $claims->expiresAt() <= $skewedPast
        ) {
            return null;
        }

        if (
            $claims->notBefore() !== null
            && $claims->notBefore() > $skewedFuture
        ) {
            return null;
        }

        if (
            $this->policy->requiredIssuer() !== null
            && $claims->issuer() !== $this->policy->requiredIssuer()
        ) {
            return null;
        }

        $acceptedAudiences = $this->policy->acceptedAudiences();
        if (
            $acceptedAudiences !== []
            && array_intersect(
                $acceptedAudiences,
                $claims->audiences()
            ) === []
        ) {
            return null;
        }

        if ($claims->scope() === null) {
            $scopes = [];
        } else {
            $scopes = preg_split(
                '/\s+/',
                trim($claims->scope())
            ) ?: [];
        }

        return new ValidatedAccessToken(
            new IdentityId($claims->subject()),
            new ScopeSet($scopes),
            $claims->expiresAt(),
            $claims->issuedAt(),
            $claims->additional()
        );
    }
}
