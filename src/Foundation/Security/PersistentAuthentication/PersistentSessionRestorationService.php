<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

use DateTimeImmutable;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Contracts\PersistentAuthenticationCredentialLifecycleStoreInterface;
use Sif\Foundation\Security\Contracts\PersistentAuthenticationPrincipalFactoryInterface;
use Sif\Foundation\Security\Session\SessionAuthenticationManager;
use Sif\Foundation\Session\SessionState;

final readonly class PersistentSessionRestorationService
{
    public function __construct(
        private PersistentAuthenticationCredentialLifecycleStoreInterface $store,
        private PersistentAuthenticationService $persistentAuthentication,
        private PersistentAuthenticationPrincipalFactoryInterface $principalFactory,
        private SessionAuthenticationManager $sessionAuthentication,
        private AuthenticationLevel $restoredLevel
    ) {
    }

    public function restore(
        PersistentAuthenticationToken $presented,
        SessionState $session,
        SecurityContext $securityContext,
        DateTimeImmutable $now
    ): PersistentSessionRestorationResult {
        if ($securityContext->isAuthenticated()) {
            return PersistentSessionRestorationResult::rejected(
                PersistentSessionRestorationStatus::IdentityUnavailable
            );
        }

        $credential = $this->store->findBySelector($presented->selector());

        if ($credential === null) {
            return PersistentSessionRestorationResult::rejected(
                PersistentSessionRestorationStatus::Missing
            );
        }

        $validation = $this->persistentAuthentication->validateAndRotate(
            $presented,
            $now
        );

        if (!$validation->isAccepted()) {
            return PersistentSessionRestorationResult::rejected(
                self::mapStatus($validation->status())
            );
        }

        $replacement = $validation->replacementToken();
        if ($replacement === null) {
            return PersistentSessionRestorationResult::rejected(
                PersistentSessionRestorationStatus::ReplaySuspected
            );
        }

        $principal = $this->principalFactory->create(
            $credential->identityId(),
            new AuthenticationEvidence(
                new AuthenticationMethod('persistent'),
                $this->restoredLevel,
                $now
            )
        );

        if ($principal === null) {
            $this->persistentAuthentication->revoke(
                $credential->selector(),
                $now
            );

            return PersistentSessionRestorationResult::rejected(
                PersistentSessionRestorationStatus::IdentityUnavailable
            );
        }

        $this->sessionAuthentication->authenticate(
            $principal,
            $session,
            $securityContext
        );

        return PersistentSessionRestorationResult::restored(
            $principal,
            $replacement
        );
    }

    private static function mapStatus(
        PersistentAuthenticationValidationStatus $status
    ): PersistentSessionRestorationStatus {
        return match ($status) {
            PersistentAuthenticationValidationStatus::Missing =>
                PersistentSessionRestorationStatus::Missing,
            PersistentAuthenticationValidationStatus::Expired =>
                PersistentSessionRestorationStatus::Expired,
            PersistentAuthenticationValidationStatus::Revoked =>
                PersistentSessionRestorationStatus::Revoked,
            PersistentAuthenticationValidationStatus::ReplaySuspected,
            PersistentAuthenticationValidationStatus::Accepted =>
                PersistentSessionRestorationStatus::ReplaySuspected,
        };
    }
}
