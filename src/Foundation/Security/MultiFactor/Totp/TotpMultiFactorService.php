<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use DateTimeImmutable;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Contracts\MultiFactorChallengeLifecycleStoreInterface;
use Sif\Foundation\Security\Contracts\TotpFactorStoreInterface;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallenge;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengeId;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengePurpose;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengeStatus;
use Sif\Foundation\Security\MultiFactor\MultiFactorSatisfactionResult;
use Sif\Foundation\Security\MultiFactor\MultiFactorType;

final readonly class TotpMultiFactorService
{
    public function __construct(
        private MultiFactorChallengeLifecycleStoreInterface $challengeStore,
        private TotpFactorStoreInterface $factorStore,
        private TotpFactorVerifier $factorVerifier
    ) {
    }

    public function issue(
        AuthenticatedPrincipal $principal,
        MultiFactorChallengePurpose $purpose,
        AuthenticationLevel $requiredLevel,
        DateTimeImmutable $issuedAt,
        int $ttlSeconds = 300
    ): ?MultiFactorChallenge {
        if ($ttlSeconds < 30 || $ttlSeconds > 1800) {
            throw new \InvalidArgumentException('MFA challenge TTL must be between 30 and 1800 seconds.');
        }
        if ($principal->evidence()->level()->satisfies($requiredLevel)) {
            return null;
        }
        if ($this->factorStore->findActiveForIdentity($principal->identity()->id()) === null) {
            return null;
        }
        $this->challengeStore->revokePendingForIdentity($principal->identity()->id(), $purpose);
        $challenge = new MultiFactorChallenge(
            new MultiFactorChallengeId(bin2hex(random_bytes(16))),
            $principal->identity()->id(),
            MultiFactorType::totp(),
            $purpose,
            $requiredLevel,
            $issuedAt,
            $issuedAt->modify(sprintf('+%d seconds', $ttlSeconds))
        );
        $this->challengeStore->save($challenge);
        return $challenge;
    }

    public function satisfy(
        AuthenticatedPrincipal $principal,
        MultiFactorChallengeId $challengeId,
        TotpCode $code,
        DateTimeImmutable $at
    ): MultiFactorSatisfactionResult {
        $challenge = $this->challengeStore->find($challengeId);
        if ($challenge === null
            || $challenge->status() !== MultiFactorChallengeStatus::Pending
            || $challenge->isExpiredAt($at)
            || !$challenge->factorType()->equals(MultiFactorType::totp())
            || $challenge->identityId()->value() !== $principal->identity()->id()->value()) {
            return MultiFactorSatisfactionResult::rejected();
        }
        if (!$this->factorVerifier->verify($principal->identity()->id(), $code, $at)->isVerified()) {
            return MultiFactorSatisfactionResult::rejected();
        }
        if (!$this->challengeStore->satisfy($challengeId, $at)) {
            return MultiFactorSatisfactionResult::rejected();
        }
        $level = new AuthenticationLevel(max(
            $principal->evidence()->level()->value(),
            $challenge->requiredLevel()->value()
        ));
        $upgraded = new AuthenticatedPrincipal(
            $principal->identity(),
            $principal->attributes(),
            new AuthenticationEvidence(new AuthenticationMethod('mfa.totp'), $level, $at)
        );
        return MultiFactorSatisfactionResult::satisfied($upgraded);
    }
}
