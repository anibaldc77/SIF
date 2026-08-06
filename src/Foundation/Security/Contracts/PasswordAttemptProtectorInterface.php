<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\Password\Protection\PasswordAttemptDecision;
use Sif\Foundation\Security\Password\Protection\PasswordAttemptKey;

interface PasswordAttemptProtectorInterface
{
    public function inspect(PasswordAttemptKey $key, DateTimeImmutable $attemptedAt): PasswordAttemptDecision;

    public function recordFailure(PasswordAttemptKey $key, DateTimeImmutable $attemptedAt): void;

    public function recordSuccess(PasswordAttemptKey $key, DateTimeImmutable $attemptedAt): void;
}
