<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Transport;

use Sif\Foundation\Session\SessionState;

final readonly class SessionTransportResult
{
    public function __construct(
        private SessionState $state,
        private bool $identifierAccepted,
        private bool $expiredRecordDiscarded,
    ) {
    }

    public function state(): SessionState { return $this->state; }
    public function identifierAccepted(): bool { return $this->identifierAccepted; }
    public function expiredRecordDiscarded(): bool { return $this->expiredRecordDiscarded; }
    public function shouldIssueCookie(): bool
    {
        return $this->state->isNew() || !$this->identifierAccepted || $this->expiredRecordDiscarded;
    }
}
