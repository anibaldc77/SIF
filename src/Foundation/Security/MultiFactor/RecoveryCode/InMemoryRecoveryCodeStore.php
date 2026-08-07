<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\RecoveryCode;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\RecoveryCodeStoreInterface;
use Sif\Foundation\Security\Identity\IdentityId;

final class InMemoryRecoveryCodeStore implements RecoveryCodeStoreInterface
{
    /** @var array<string, list<RecoveryCodeRecord>> */
    private array $records = [];

    public function replaceForIdentity(IdentityId $identityId, array $records): void
    {
        foreach ($records as $record) {
            if ($record->identityId()->value() !== $identityId->value()) {
                throw new \InvalidArgumentException('Recovery code record belongs to another identity.');
            }
        }

        $this->records[$identityId->value()] = array_values($records);
    }

    public function hasAvailableForIdentity(IdentityId $identityId): bool
    {
        foreach ($this->records[$identityId->value()] ?? [] as $record) {
            if (!$record->isConsumed()) {
                return true;
            }
        }

        return false;
    }

    public function consume(
        IdentityId $identityId,
        RecoveryCodeDigest $digest,
        DateTimeImmutable $consumedAt
    ): bool {
        $records = $this->records[$identityId->value()] ?? [];

        foreach ($records as $index => $record) {
            if (!$record->isConsumed() && $record->digest()->equals($digest)) {
                $records[$index] = $record->consume($consumedAt);
                $this->records[$identityId->value()] = $records;

                return true;
            }
        }

        return false;
    }

    /** @return list<RecoveryCodeRecord> */
    public function recordsForIdentity(IdentityId $identityId): array
    {
        return $this->records[$identityId->value()] ?? [];
    }
}
