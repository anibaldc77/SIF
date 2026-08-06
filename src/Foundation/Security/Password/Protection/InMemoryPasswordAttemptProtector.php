<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Protection;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Security\Contracts\PasswordAttemptProtectorInterface;

final class InMemoryPasswordAttemptProtector implements PasswordAttemptProtectorInterface
{
    /** @var array<string, list<int>> */
    private array $failures = [];

    /** @var array<string, int> */
    private array $blockedUntil = [];

    public function __construct(private readonly PasswordAttemptPolicy $policy)
    {
    }

    public function inspect(PasswordAttemptKey $key, DateTimeImmutable $attemptedAt): PasswordAttemptDecision
    {
        $fingerprint = $key->fingerprint();
        $timestamp = $attemptedAt->getTimestamp();
        $blockedUntil = $this->blockedUntil[$fingerprint] ?? null;

        if ($blockedUntil !== null && $timestamp < $blockedUntil) {
            return PasswordAttemptDecision::blockUntil(
                (new DateTimeImmutable())->setTimestamp($blockedUntil)->setTimezone(new DateTimeZone('UTC'))
            );
        }

        if ($blockedUntil !== null) {
            unset($this->blockedUntil[$fingerprint], $this->failures[$fingerprint]);
        }

        $this->prune($fingerprint, $timestamp);

        return PasswordAttemptDecision::allow();
    }

    public function recordFailure(PasswordAttemptKey $key, DateTimeImmutable $attemptedAt): void
    {
        $fingerprint = $key->fingerprint();
        $timestamp = $attemptedAt->getTimestamp();
        $this->prune($fingerprint, $timestamp);
        $this->failures[$fingerprint][] = $timestamp;

        if (count($this->failures[$fingerprint]) >= $this->policy->maximumFailures()) {
            $this->blockedUntil[$fingerprint] = $timestamp + $this->policy->lockoutSeconds();
        }
    }

    public function recordSuccess(PasswordAttemptKey $key, DateTimeImmutable $attemptedAt): void
    {
        $fingerprint = $key->fingerprint();
        unset($this->failures[$fingerprint], $this->blockedUntil[$fingerprint]);
    }

    private function prune(string $fingerprint, int $timestamp): void
    {
        $minimumTimestamp = $timestamp - $this->policy->observationWindowSeconds();
        $failures = $this->failures[$fingerprint] ?? [];
        $failures = array_values(array_filter(
            $failures,
            static fn (int $failureTimestamp): bool => $failureTimestamp > $minimumTimestamp
        ));

        if ($failures === []) {
            unset($this->failures[$fingerprint]);

            return;
        }

        $this->failures[$fingerprint] = $failures;
    }
}
