<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Introspection;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\AccessTokenValidatorInterface;
use Sif\Foundation\Security\Contracts\TokenIntrospectorInterface;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\OAuth2\AccessToken;
use Sif\Foundation\Security\OAuth2\ValidatedAccessToken;

final readonly class OpaqueAccessTokenValidator implements AccessTokenValidatorInterface
{
    public function __construct(
        private TokenIntrospectorInterface $introspector
    ) {
    }

    public function validate(
        AccessToken $token,
        DateTimeImmutable $now
    ): ?ValidatedAccessToken {
        $result = $this->introspector->introspect($token);

        if (!$result->isActive()) {
            return null;
        }

        $subject = $result->subject();
        if ($subject === null || $subject === '') {
            return null;
        }

        if (
            $result->expiresAt() !== null
            && $result->expiresAt() <= $now
        ) {
            return null;
        }

        return new ValidatedAccessToken(
            new IdentityId($subject),
            $result->scopes(),
            $result->expiresAt() ?? $now->modify('+5 minutes'),
            $result->issuedAt(),
            $result->attributes()
        );
    }
}
