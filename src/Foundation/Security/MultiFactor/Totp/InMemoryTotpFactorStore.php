<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\TotpFactorStoreInterface;
use Sif\Foundation\Security\Identity\IdentityId;

final class InMemoryTotpFactorStore implements TotpFactorStoreInterface
{
    /** @var array<string, TotpFactorRecord> */
    private array $factors = [];

    public function __construct(private ?DateTimeImmutable $activationTime = null)
    {
    }

    public function save(TotpFactorRecord $factor): void
    {
        $this->factors[$factor->id()->value()] = $factor;
    }

    public function find(TotpFactorId $id): ?TotpFactorRecord
    {
        return $this->factors[$id->value()] ?? null;
    }

    public function findActiveForIdentity(IdentityId $identityId): ?TotpFactorRecord
    {
        foreach ($this->factors as $factor) {
            if (
                $factor->status() === TotpFactorStatus::Active
                && $factor->identityId()->value() === $identityId->value()
            ) {
                return $factor;
            }
        }

        return null;
    }

    public function activate(TotpFactorId $id, int $acceptedCounter): bool
    {
        $factor = $this->find($id);

        if ($factor === null || $factor->status() !== TotpFactorStatus::Pending) {
            return false;
        }

        $this->factors[$id->value()] = $factor->activate(
            $this->activationTime ?? new DateTimeImmutable('now'),
            $acceptedCounter
        );

        return true;
    }

    public function acceptCounter(TotpFactorId $id, int $acceptedCounter): bool
    {
        $factor = $this->find($id);

        if (
            $factor === null
            || $factor->status() !== TotpFactorStatus::Active
            || ($factor->lastAcceptedCounter() !== null && $acceptedCounter <= $factor->lastAcceptedCounter())
        ) {
            return false;
        }

        $this->factors[$id->value()] = $factor->withAcceptedCounter($acceptedCounter);

        return true;
    }

    public function revoke(TotpFactorId $id): bool
    {
        $factor = $this->find($id);

        if ($factor === null || $factor->status() === TotpFactorStatus::Revoked) {
            return false;
        }

        $this->factors[$id->value()] = $factor->revoke();

        return true;
    }
}
