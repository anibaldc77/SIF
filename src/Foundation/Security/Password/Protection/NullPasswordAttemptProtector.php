<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Protection;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\PasswordAttemptProtectorInterface;

final readonly class NullPasswordAttemptProtector implements PasswordAttemptProtectorInterface
{
    public function inspect(PasswordAttemptKey $key, DateTimeImmutable $attemptedAt): PasswordAttemptDecision
    {
        return PasswordAttemptDecision::allow();
    }

    public function recordFailure(PasswordAttemptKey $key, DateTimeImmutable $attemptedAt): void
    {
    }

    public function recordSuccess(PasswordAttemptKey $key, DateTimeImmutable $attemptedAt): void
    {
    }
}
