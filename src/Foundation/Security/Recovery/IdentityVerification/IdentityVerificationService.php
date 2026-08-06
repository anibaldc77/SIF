<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\IdentityVerification;

use DateInterval;
use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\IdentityProviderInterface;
use Sif\Foundation\Security\Contracts\IdentityVerificationActivatorInterface;
use Sif\Foundation\Security\Contracts\RecoveryChallengeDeliveryInterface;
use Sif\Foundation\Security\Contracts\RecoveryChallengeStoreInterface;
use Sif\Foundation\Security\Contracts\RecoveryTokenGeneratorInterface;
use Sif\Foundation\Security\Contracts\RecoveryRequestProtectorInterface;
use Sif\Foundation\Security\Contracts\RecoverySecurityEventHandlerInterface;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;
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

final readonly class IdentityVerificationService
{
    public function __construct(
        private IdentityProviderInterface $identityProvider,
        private RecoveryTokenGeneratorInterface $tokenGenerator,
        private RecoveryChallengeStoreInterface $challengeStore,
        private RecoveryChallengeDeliveryInterface $delivery,
        private IdentityVerificationActivatorInterface $activator,
        private DateInterval $lifetime = new DateInterval('PT30M'),
        private RecoveryRequestProtectorInterface $requestProtector = new NullRecoveryRequestProtector(),
        private RecoverySecurityEventHandlerInterface $events = new NullRecoverySecurityEventHandler()
    ) {
    }

    public function request(
        IdentityLookupKey $lookupKey,
        DateTimeImmutable $requestedAt
    ): IdentityVerificationRequestResult {
        $requestKey = new RecoveryRequestKey($this->identityProvider->id(), $lookupKey, RecoveryChallengePurpose::IdentityVerification);
        $decision = $this->requestProtector->assess($requestKey, $requestedAt);
        if (!$decision->isAllowed()) {
            $this->events->handle(new RecoverySecurityEvent(
                RecoverySecurityEventType::RequestBlocked,
                RecoveryChallengePurpose::IdentityVerification,
                $requestKey->fingerprint(),
                $requestedAt
            ));

            return new IdentityVerificationRequestResult();
        }
        $this->requestProtector->record($requestKey, $requestedAt);
        $this->events->handle(new RecoverySecurityEvent(
            RecoverySecurityEventType::RequestAccepted,
            RecoveryChallengePurpose::IdentityVerification,
            $requestKey->fingerprint(),
            $requestedAt
        ));

        $identityResult = $this->identityProvider->resolve($lookupKey);

        if (!$identityResult->wasFound() || !$identityResult->record()->isActive()) {
            return new IdentityVerificationRequestResult();
        }

        $identity = $identityResult->record()->identity();
        $subject = new RecoverySubjectKey($identity->id()->value());

        $this->challengeStore->revokeOutstanding(
            $subject,
            RecoveryChallengePurpose::IdentityVerification,
            $requestedAt
        );

        $token = $this->tokenGenerator->generate();
        $challenge = new RecoveryChallenge(
            $this->newChallengeId(),
            RecoveryChallengePurpose::IdentityVerification,
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
            RecoveryChallengePurpose::IdentityVerification,
            $subject->fingerprint(),
            $requestedAt,
            $challenge->id()
        ));

        return new IdentityVerificationRequestResult();
    }

    public function confirm(
        RecoveryChallengeId $challengeId,
        RecoveryToken $token,
        DateTimeImmutable $confirmedAt
    ): IdentityVerificationConfirmationResult {
        $consumption = $this->challengeStore->consume(
            $challengeId,
            RecoveryChallengePurpose::IdentityVerification,
            $token,
            $confirmedAt
        );

        if (!$consumption->isConsumed()) {
            $this->events->handle(new RecoverySecurityEvent(
                RecoverySecurityEventType::ChallengeRejected,
                RecoveryChallengePurpose::IdentityVerification,
                hash('sha256', $challengeId->value()),
                $confirmedAt,
                $challengeId
            ));

            return IdentityVerificationConfirmationResult::rejected();
        }

        $record = $consumption->record();
        if ($record === null) {
            return IdentityVerificationConfirmationResult::rejected();
        }

        $identity = new Identity(new IdentityId($record->challenge()->subject()->value()));
        $this->activator->markVerified($identity);

        $this->events->handle(new RecoverySecurityEvent(
            RecoverySecurityEventType::ChallengeConsumed,
            RecoveryChallengePurpose::IdentityVerification,
            $record->challenge()->subject()->fingerprint(),
            $confirmedAt,
            $challengeId
        ));

        return IdentityVerificationConfirmationResult::succeeded();
    }

    private function newChallengeId(): RecoveryChallengeId
    {
        return new RecoveryChallengeId('identity-verification-' . bin2hex(random_bytes(16)));
    }
}
