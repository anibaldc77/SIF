<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Policy;

use Sif\Foundation\Session\SessionPolicy;

final readonly class SessionLifecyclePolicy
{
    public function __construct(
        private SessionExpirationPolicy $expiration = new SessionExpirationPolicy(new SessionPolicy()),
        private SessionRegenerationPolicy $regeneration = new SessionRegenerationPolicy(),
    ) {
    }

    public function expiration(): SessionExpirationPolicy { return $this->expiration; }
    public function regeneration(): SessionRegenerationPolicy { return $this->regeneration; }
}
