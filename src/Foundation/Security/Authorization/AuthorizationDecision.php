<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization;

final readonly class AuthorizationDecision
{
    private function __construct(private bool $allowed, private AuthorizationFailureReason $reason)
    {
    }

    public static function allow(): self { return new self(true, AuthorizationFailureReason::NONE); }
    public static function deny(AuthorizationFailureReason $reason = AuthorizationFailureReason::NOT_AUTHORIZED): self
    {
        return new self(false, $reason === AuthorizationFailureReason::NONE ? AuthorizationFailureReason::NOT_AUTHORIZED : $reason);
    }

    public function isAllowed(): bool { return $this->allowed; }
    public function reason(): AuthorizationFailureReason { return $this->reason; }
}
