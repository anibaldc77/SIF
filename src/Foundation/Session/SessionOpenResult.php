<?php

declare(strict_types=1);

namespace Sif\Foundation\Session;

final readonly class SessionOpenResult
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
}
