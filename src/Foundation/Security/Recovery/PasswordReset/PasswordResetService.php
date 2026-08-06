<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\PasswordReset;

use DateInterval;
use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\IdentityProviderInterface;
use Sif\Foundation\Security\Contracts\PasswordHasherInterface;
use Sif\Foundation\Security\Contracts\PasswordHashStoreInterface;
use Sif\Foundation\Security\Contracts\RecoveryChallengeDeliveryInterface;
use Sif\Foundation\Security\Contracts\RecoveryChallengeStoreInterface;
use Sif\Foundation\Security\Contracts\RecoveryTokenGeneratorInterface;
use Sif\Foundation\Security\Contracts\RecoveryRequestProtectorInterface;
use Sif\Foundation\Security\Contracts\RecoverySecurityEventHandlerInterface;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;
use Sif\Foundation\Security\Password\PasswordSecret;
use Sif\Foundation\Security\Recovery\RecoveryChallenge;
use Sif\Foundation\Security\Recovery\RecoveryChallengeId;
use Sif\Foundation\Security\Recovery\RecoveryChallengePurpose;
use Sif\Foundation\Security\Recovery\RecoveryChallengeRecord;
use Sif\Foundation\Security\Recovery\RecoverySubjectKey;
use Sif\Foundation\Security\Recovery\RecoveryToken;
use Sif\Foundation\Security\Recovery\RecoveryTokenDigest;
use Sif\Foundation\Security\Recovery\Events\NullRecoverySecurityEventHandler;
use Sif\Foundation\Security\Recovery\Events\RecoverySecurityEvent;
use Sif\Foundation\Security\Recovery\Events\RecoverySecurityEventType;
use Sif\Foundation\Security\Recovery\Protection\NullRecoveryRequestProtector;
use Sif\Foundation\Security\Recovery\Protection\RecoveryRequestKey;

final readonly class PasswordResetService
{
    public function __construct(
        private IdentityProviderInterface $identityProvider,
        private RecoveryTokenGeneratorInterface $tokenGenerator,
        private RecoveryChallengeStoreInterface $challengeStore,
        private RecoveryChallengeDeliveryInterface $delivery,
        private PasswordHasherInterface $passwordHasher,
        private PasswordHashStoreInterface $passwordHashStore,
        private DateInterval $lifetime = new DateInterval('PT15M'),
        private RecoveryRequestProtectorInterface $requestProtector = new NullRecoveryRequestProtector(),
        private RecoverySecurityEventHandlerInterface $events = new NullRecoverySecurityEventHandler()
    ) {
    }

    public function request(IdentityLookupKey $lookupKey, DateTimeImmutable $requestedAt): PasswordResetRequestResult
    {
        $requestKey = new RecoveryRequestKey($this->identityProvider->id(), $lookupKey, RecoveryChallengePurpose::PasswordReset);
        $decision = $this->requestProtector->assess($requestKey, $requestedAt);
        if (!$decision->isAllowed()) {
            $this->events->handle(new RecoverySecurityEvent(
                RecoverySecurityEventType::RequestBlocked,
                RecoveryChallengePurpose::PasswordReset,
                $requestKey->fingerprint(),
                $requestedAt
            ));

            return new PasswordResetRequestResult();
        }
        $this->requestProtector->record($requestKey, $requestedAt);
        $this->events->handle(new RecoverySecurityEvent(
            RecoverySecurityEventType::RequestAccepted,
            RecoveryChallengePurpose::PasswordReset,
            $requestKey->fingerprint(),
            $requestedAt
        ));

        $identityResult = $this->identityProvider->resolve($lookupKey);

        if (!$identityResult->wasFound() || !$identityResult->record()->isActive()) {
            return new PasswordResetRequestResult();
        }

        $identity = $identityResult->record()->identity();
        $subject = new RecoverySubjectKey($identity->id()->value());
        $this->challengeStore->revokeOutstanding(
            $subject,
            RecoveryChallengePurpose::PasswordReset,
            $requestedAt
        );

        $token = $this->tokenGenerator->generate();
        $challenge = new RecoveryChallenge(
            $this->newChallengeId(),
            RecoveryChallengePurpose::PasswordReset,
            $subject,
            $requestedAt,
            $requestedAt->add($this->lifetime)
        );

        $this->challengeStore->issue(new RecoveryChallengeRecord(
            $challenge,
            RecoveryTokenDigest::fromToken($token)
        ));
        $this->delivery->deliver($identity, $challenge, $token);
        $this->events->handle(new RecoverySecurityEvent(
            RecoverySecurityEventType::ChallengeIssued,
            RecoveryChallengePurpose::PasswordReset,
            $subject->fingerprint(),
            $requestedAt,
            $challenge->id()
        ));

        return new PasswordResetRequestResult();
    }

    public function confirm(
        RecoveryChallengeId $challengeId,
        RecoveryToken $token,
        PasswordSecret $replacement,
        DateTimeImmutable $confirmedAt
    ): PasswordResetConfirmationResult {
        $replacementHash = $this->passwordHasher->hash($replacement);
        $consumption = $this->challengeStore->consume(
            $challengeId,
            RecoveryChallengePurpose::PasswordReset,
            $token,
            $confirmedAt
        );

        if (!$consumption->isConsumed()) {
            $this->events->handle(new RecoverySecurityEvent(
                RecoverySecurityEventType::ChallengeRejected,
                RecoveryChallengePurpose::PasswordReset,
                hash('sha256', $challengeId->value()),
                $confirmedAt,
                $challengeId
            ));

            return PasswordResetConfirmationResult::rejected();
        }

        $record = $consumption->record();
        if ($record === null) {
            return PasswordResetConfirmationResult::rejected();
        }

        $identity = new Identity(new IdentityId($record->challenge()->subject()->value()));
        $this->passwordHashStore->replaceFor($identity, $replacementHash);

        $this->events->handle(new RecoverySecurityEvent(
            RecoverySecurityEventType::ChallengeConsumed,
            RecoveryChallengePurpose::PasswordReset,
            $record->challenge()->subject()->fingerprint(),
            $confirmedAt,
            $challengeId
        ));

        return PasswordResetConfirmationResult::succeeded();
    }

    private function newChallengeId(): RecoveryChallengeId
    {
        return new RecoveryChallengeId('password-reset-' . bin2hex(random_bytes(16)));
    }
}
