<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Http;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\AccessTokenValidatorInterface;
use Sif\Foundation\Security\Contracts\BearerTokenExtractorInterface;
use Sif\Foundation\Security\Exceptions\InvalidAccessTokenException;
use Sif\Foundation\Security\Exceptions\InvalidBearerTokenException;
use Sif\Foundation\Security\OAuth2\BearerAuthenticationFailureFactory;

final readonly class OAuth2ResourceServerAuthenticator
{
    public function __construct(
        private BearerTokenExtractorInterface $extractor,
        private AccessTokenValidatorInterface $validator,
        private BearerPrincipalFactory $principalFactory,
        private BearerAuthenticationFailureFactory $failureFactory,
        private string $realm
    ) {
    }

    public function authenticate(
        string $authorizationHeader,
        DateTimeImmutable $now
    ): ResourceServerAuthenticationResult {
        try {
            $token = $this->extractor->extract($authorizationHeader);
        } catch (InvalidBearerTokenException | InvalidAccessTokenException $exception) {
            return ResourceServerAuthenticationResult::failed(
                $this->failureFactory->invalidRequest(
                    $this->realm,
                    'Malformed Bearer authentication request.'
                )
            );
        }

        if ($token === null) {
            return ResourceServerAuthenticationResult::failed(
                $this->failureFactory->invalidToken(
                    $this->realm,
                    'Bearer access token is required.'
                )
            );
        }

        $validated = $this->validator->validate($token, $now);

        if ($validated === null) {
            return ResourceServerAuthenticationResult::failed(
                $this->failureFactory->invalidToken(
                    $this->realm,
                    'Bearer access token is invalid or expired.'
                )
            );
        }

        return ResourceServerAuthenticationResult::authenticated(
            $this->principalFactory->create($validated, $now),
            $validated
        );
    }
}
