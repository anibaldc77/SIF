<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\PersistentAuthenticationCredentialLifecycleStoreInterface;

final class InMemoryPersistentAuthenticationCredentialStore implements PersistentAuthenticationCredentialLifecycleStoreInterface
{
    /** @var array<string, PersistentAuthenticationCredential> */
    private array $credentials = [];

    public function save(PersistentAuthenticationCredential $credential): void
    {
        $this->credentials[$credential->selector()->value()] = $credential;
    }

    public function findBySelector(
        PersistentAuthenticationSelector $selector
    ): ?PersistentAuthenticationCredential {
        return $this->credentials[$selector->value()] ?? null;
    }

    public function rotate(
        PersistentAuthenticationSelector $selector,
        PersistentAuthenticationValidatorDigest $currentDigest,
        PersistentAuthenticationCredential $replacement
    ): bool {
        $current = $this->credentials[$selector->value()] ?? null;

        if (
            $current === null
            || $current->status() !== PersistentAuthenticationCredentialStatus::Active
            || !$current->validatorDigest()->equals($currentDigest)
            || !$replacement->selector()->equals($selector)
            || $replacement->identityId()->value() !== $current->identityId()->value()
        ) {
            return false;
        }

        $this->credentials[$selector->value()] = $replacement;

        return true;
    }

    public function revoke(
        PersistentAuthenticationSelector $selector,
        DateTimeImmutable $revokedAt
    ): bool {
        $current = $this->credentials[$selector->value()] ?? null;

        if (
            $current === null
            || $current->status() !== PersistentAuthenticationCredentialStatus::Active
        ) {
            return false;
        }

        $this->credentials[$selector->value()] = $current->revoke($revokedAt);

        return true;
    }
}
