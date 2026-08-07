<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Federation;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\FederatedSecurityEventPublisherInterface;
use Sif\Foundation\Security\Contracts\FederatedSessionEstablisherInterface;
use Sif\Foundation\Security\Contracts\OidcTokenExchangerInterface;
use Sif\Foundation\Security\Oidc\OidcAuthorizationCallback;
use Sif\Foundation\Security\Oidc\OidcAuthorizationCallbackValidator;
use Sif\Foundation\Security\Oidc\OidcAuthorizationTransaction;
use Sif\Foundation\Security\Oidc\OidcClientRegistration;
use Sif\Foundation\Security\Oidc\OidcClientSecret;
use Sif\Foundation\Security\Oidc\OidcIdTokenValidator;
use Sif\Foundation\Security\Oidc\OidcTokenExchangeRequestFactory;

final readonly class FederatedLoginOrchestrator
{
    public function __construct(
        private OidcAuthorizationCallbackValidator $callbackValidator,
        private OidcTokenExchangeRequestFactory $tokenExchangeRequestFactory,
        private OidcTokenExchangerInterface $tokenExchanger,
        private OidcIdTokenValidator $idTokenValidator,
        private FederatedAuthenticationMapper $authenticationMapper,
        private FederatedSessionEstablisherInterface $sessionEstablisher,
        private FederatedSecurityEventPublisherInterface $eventPublisher
    ) {
    }

    public function complete(
        OidcAuthorizationTransaction $transaction,
        OidcAuthorizationCallback $callback,
        OidcClientRegistration $registration,
        DateTimeImmutable $now,
        ?OidcClientSecret $clientSecret = null
    ): ?FederatedLoginResult {
        $this->callbackValidator->validate(
            $transaction,
            $callback
        );

        $exchangeRequest = $this->tokenExchangeRequestFactory->create(
            $transaction,
            $callback,
            $registration,
            $clientSecret
        );

        $exchangeResult = $this->tokenExchanger->exchange(
            $exchangeRequest
        );

        $validatedIdToken = $this->idTokenValidator->validate(
            $exchangeResult->idToken(),
            $transaction->nonce(),
            $now
        );

        if ($validatedIdToken === null) {
            $this->eventPublisher->publish(
                new FederatedSecurityEvent(
                    'oidc.login.id_token_rejected',
                    $now
                )
            );

            return null;
        }

        $mapping = $this->authenticationMapper->map(
            $validatedIdToken->identity(),
            $now
        );

        if ($mapping === null) {
            $this->eventPublisher->publish(
                new FederatedSecurityEvent(
                    'oidc.login.account_unresolved',
                    $now,
                    [
                        'issuer' => $validatedIdToken
                            ->identity()
                            ->issuer(),
                        'subject' => $validatedIdToken
                            ->identity()
                            ->subject(),
                    ]
                )
            );

            return null;
        }

        $this->sessionEstablisher->establish(
            $mapping->principal()
        );

        $this->eventPublisher->publish(
            new FederatedSecurityEvent(
                'oidc.login.succeeded',
                $now,
                [
                    'identity_id' => $mapping
                        ->principal()
                        ->identity()
                        ->id()
                        ->value(),
                    'issuer' => $mapping
                        ->federatedIdentity()
                        ->issuer(),
                ]
            )
        );

        return new FederatedLoginResult(
            $mapping->principal(),
            $mapping->federatedIdentity()
        );
    }
}
