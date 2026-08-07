<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\TrustedDevice;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\TrustedDeviceGrantLifecycleStoreInterface;
use Sif\Foundation\Security\Identity\IdentityId;

final class InMemoryTrustedDeviceGrantStore implements TrustedDeviceGrantLifecycleStoreInterface
{
    /** @var array<string, TrustedDeviceGrant> */
    private array $grants = [];

    public function save(TrustedDeviceGrant $grant): void
    {
        $this->grants[$grant->id()->value()] = $grant;
    }

    public function find(TrustedDeviceGrantId $id): ?TrustedDeviceGrant
    {
        return $this->grants[$id->value()] ?? null;
    }

    public function findActiveForIdentity(IdentityId $identityId): array
    {
        $matches = [];

        foreach ($this->grants as $grant) {
            if (
                $grant->identityId()->value() === $identityId->value()
                && $grant->status() === TrustedDeviceGrantStatus::Active
            ) {
                $matches[] = $grant;
            }
        }

        return $matches;
    }

    public function revoke(
        TrustedDeviceGrantId $id,
        DateTimeImmutable $revokedAt
    ): bool {
        $grant = $this->grants[$id->value()] ?? null;

        if (
            $grant === null
            || $grant->status() === TrustedDeviceGrantStatus::Revoked
        ) {
            return false;
        }

        $this->grants[$id->value()] = $grant->revoke($revokedAt);

        return true;
    }

    public function revokeAllForIdentity(
        IdentityId $identityId,
        DateTimeImmutable $revokedAt
    ): int {
        $count = 0;

        foreach ($this->grants as $key => $grant) {
            if (
                $grant->identityId()->value() === $identityId->value()
                && $grant->status() === TrustedDeviceGrantStatus::Active
            ) {
                $this->grants[$key] = $grant->revoke($revokedAt);
                $count++;
            }
        }

        return $count;
    }
}
