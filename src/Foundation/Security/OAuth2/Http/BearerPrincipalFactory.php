<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Http;

use DateTimeImmutable;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\OAuth2\ValidatedAccessToken;

final readonly class BearerPrincipalFactory
{
    public function create(
        ValidatedAccessToken $token,
        DateTimeImmutable $authenticatedAt
    ): AuthenticatedPrincipal {
        return new AuthenticatedPrincipal(
            new Identity($token->subject()),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('oauth2-bearer'),
                new AuthenticationLevel(50),
                $authenticatedAt
            )
        );
    }
}
