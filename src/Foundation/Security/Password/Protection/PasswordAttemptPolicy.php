<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Protection;

use Sif\Foundation\Security\Exceptions\InvalidPasswordAttemptProtectionException;

final readonly class PasswordAttemptPolicy
{
    public function __construct(
        private int $maximumFailures,
        private int $observationWindowSeconds,
        private int $lockoutSeconds
    ) {
        if ($maximumFailures < 1) {
            throw new InvalidPasswordAttemptProtectionException('Maximum failures must be at least one.');
        }

        if ($observationWindowSeconds < 1 || $lockoutSeconds < 1) {
            throw new InvalidPasswordAttemptProtectionException(
                'Observation window and lockout duration must be positive.'
            );
        }
    }

    public function maximumFailures(): int
    {
        return $this->maximumFailures;
    }

    public function observationWindowSeconds(): int
    {
        return $this->observationWindowSeconds;
    }

    public function lockoutSeconds(): int
    {
        return $this->lockoutSeconds;
    }
}
