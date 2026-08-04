<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Policy;

use DateTimeImmutable;
use Sif\Foundation\Session\Exceptions\SessionException;
use Sif\Foundation\Session\SessionState;

final readonly class SessionRegenerationPolicy
{
    public function __construct(private ?int $intervalSeconds = null)
    {
        if ($intervalSeconds !== null && $intervalSeconds < 1) {
            throw new SessionException('Session regeneration interval must be a positive integer.');
        }
    }

    public function intervalSeconds(): ?int { return $this->intervalSeconds; }

    public function shouldRegenerate(SessionState $state, DateTimeImmutable $now): bool
    {
        if ($state->regenerationRequested()) {
            return true;
        }

        return $this->intervalSeconds !== null
            && $now->getTimestamp() >= $state->lastRegeneratedAt()->getTimestamp() + $this->intervalSeconds;
    }
}
