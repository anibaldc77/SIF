<?php

declare(strict_types=1);

namespace Sif\Foundation\Session;

use Sif\Foundation\Session\Exceptions\SessionException;

final readonly class SessionPolicy
{
    public function __construct(
        private int $absoluteLifetimeSeconds = 7200,
        private int $idleLifetimeSeconds = 1800,
    ) {
        if ($absoluteLifetimeSeconds < 1 || $idleLifetimeSeconds < 1) {
            throw new SessionException('Session lifetimes must be positive integers.');
        }
        if ($idleLifetimeSeconds > $absoluteLifetimeSeconds) {
            throw new SessionException('Idle lifetime must not exceed absolute lifetime.');
        }
    }

    public function absoluteLifetimeSeconds(): int { return $this->absoluteLifetimeSeconds; }
    public function idleLifetimeSeconds(): int { return $this->idleLifetimeSeconds; }
}
