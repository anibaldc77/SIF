<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use Sif\Foundation\Security\Contracts\OidcNonceGeneratorInterface;
use Sif\Foundation\Security\Contracts\OidcProviderMetadataProviderInterface;
use Sif\Foundation\Security\Contracts\OidcStateGeneratorInterface;
use Sif\Foundation\Security\Contracts\PkceCodeChallengeFactoryInterface;
use Sif\Foundation\Security\Contracts\PkceCodeVerifierGeneratorInterface;

final readonly class OidcAuthorizationRequestFactory
{
    public function __construct(
        private OidcProviderMetadataProviderInterface $metadataProvider,
        private OidcStateGeneratorInterface $stateGenerator,
        private OidcNonceGeneratorInterface $nonceGenerator,
        private PkceCodeVerifierGeneratorInterface $verifierGenerator,
        private PkceCodeChallengeFactoryInterface $challengeFactory
    ) {
    }

    /**
     * @param list<string> $scopes
     */
    public function create(
        OidcClientRegistration $registration,
        array $scopes = ['openid']
    ): OidcAuthorizationTransaction {
        $metadata = $this->metadataProvider->get();
        $state = $this->stateGenerator->generate();
        $nonce = $this->nonceGenerator->generate();
        $verifier = $this->verifierGenerator->generate();
        $challenge = $this->challengeFactory->create($verifier);

        $request = new OidcAuthorizationRequest(
            $metadata->authorizationEndpoint(),
            $registration->clientId(),
            $registration->redirectUri(),
            $state,
            $nonce,
            $challenge,
            PkceMethod::S256,
            $scopes
        );

        return new OidcAuthorizationTransaction(
            $state,
            $nonce,
            $verifier,
            $request
        );
    }
}
