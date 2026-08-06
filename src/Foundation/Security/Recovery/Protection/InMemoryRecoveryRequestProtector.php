<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\Protection;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\RecoveryRequestProtectorInterface;

final class InMemoryRecoveryRequestProtector implements RecoveryRequestProtectorInterface
{
    /** @var array<string, list<DateTimeImmutable>> */
    private array $requests = [];

    /** @var array<string, DateTimeImmutable> */
    private array $blockedUntil = [];

    public function __construct(private readonly RecoveryRequestProtectionPolicy $policy = new RecoveryRequestProtectionPolicy())
    {
    }

    public function assess(RecoveryRequestKey $key, DateTimeImmutable $instant): RecoveryRequestDecision
    {
        $fingerprint = $key->fingerprint();
        $blockedUntil = $this->blockedUntil[$fingerprint] ?? null;
        if ($blockedUntil !== null && $instant < $blockedUntil) {
            return RecoveryRequestDecision::blockUntil($blockedUntil);
        }

        $threshold = $instant->sub($this->policy->window());
        $recent = array_values(array_filter(
            $this->requests[$fingerprint] ?? [],
            static fn (DateTimeImmutable $request): bool => $request >= $threshold
        ));
        $this->requests[$fingerprint] = $recent;

        if (count($recent) >= $this->policy->maximumRequests()) {
            $blockedUntil = $instant->add($this->policy->blockDuration());
            $this->blockedUntil[$fingerprint] = $blockedUntil;

            return RecoveryRequestDecision::blockUntil($blockedUntil);
        }

        return RecoveryRequestDecision::allow();
    }

    public function record(RecoveryRequestKey $key, DateTimeImmutable $instant): void
    {
        $this->requests[$key->fingerprint()][] = $instant;
    }
}
