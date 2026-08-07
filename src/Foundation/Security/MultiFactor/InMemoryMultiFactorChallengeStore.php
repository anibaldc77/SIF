<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\MultiFactorChallengeLifecycleStoreInterface;
use Sif\Foundation\Security\Identity\IdentityId;

final class InMemoryMultiFactorChallengeStore implements MultiFactorChallengeLifecycleStoreInterface
{
    /** @var array<string, MultiFactorChallenge> */
    private array $challenges = [];

    public function save(MultiFactorChallenge $challenge): void
    {
        $this->challenges[$challenge->id()->value()] = $challenge;
    }

    public function find(MultiFactorChallengeId $id): ?MultiFactorChallenge
    {
        return $this->challenges[$id->value()] ?? null;
    }

    public function satisfy(MultiFactorChallengeId $id, DateTimeImmutable $satisfiedAt): bool
    {
        $challenge = $this->find($id);
        if ($challenge === null || $challenge->status() !== MultiFactorChallengeStatus::Pending || $challenge->isExpiredAt($satisfiedAt)) {
            return false;
        }
        $this->challenges[$id->value()] = $challenge->satisfy();
        return true;
    }

    public function revoke(MultiFactorChallengeId $id): bool
    {
        $challenge = $this->find($id);
        if ($challenge === null || $challenge->status() !== MultiFactorChallengeStatus::Pending) {
            return false;
        }
        $this->challenges[$id->value()] = $challenge->revoke();
        return true;
    }

    public function revokePendingForIdentity(IdentityId $identityId, MultiFactorChallengePurpose $purpose): int
    {
        $count = 0;
        foreach ($this->challenges as $key => $challenge) {
            if ($challenge->status() === MultiFactorChallengeStatus::Pending
                && $challenge->identityId()->value() === $identityId->value()
                && $challenge->purpose() === $purpose) {
                $this->challenges[$key] = $challenge->revoke();
                $count++;
            }
        }
        return $count;
    }
}
