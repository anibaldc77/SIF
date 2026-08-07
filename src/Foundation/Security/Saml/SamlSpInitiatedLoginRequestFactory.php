<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\SamlRelayStateGeneratorInterface;
use Sif\Foundation\Security\Contracts\SamlRequestIdGeneratorInterface;

final readonly class SamlSpInitiatedLoginRequestFactory
{
    public function __construct(
        private SamlRequestIdGeneratorInterface $requestIdGenerator,
        private SamlRelayStateGeneratorInterface $relayStateGenerator
    ) {
    }

    /**
     * @return array{request:SamlAuthnRequest,relay_state:SamlRelayState}
     */
    public function create(
        SamlServiceProviderMetadata $serviceProvider,
        SamlIdentityProviderMetadata $identityProvider,
        DateTimeImmutable $now,
        bool $forceAuthn = false
    ): array {
        $destination = $identityProvider
            ->singleSignOnServices()[0]
            ->location();

        return [
            'request' => new SamlAuthnRequest(
                $this->requestIdGenerator->generate(),
                $now,
                $serviceProvider->entityId(),
                $destination,
                $serviceProvider
                    ->assertionConsumerService()
                    ->location(),
                $forceAuthn
            ),
            'relay_state' => $this
                ->relayStateGenerator
                ->generate(),
        ];
    }
}
