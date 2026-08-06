<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Results;

final readonly class AuthenticationFailure
{
    public function __construct(private AuthenticationFailureReason $reason)
    {
    }

    public function reason(): AuthenticationFailureReason
    {
        return $this->reason;
    }

    /** @return array{reason: string} */
    public function toArray(): array
    {
        return ['reason' => $this->reason->value];
    }
}
