<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

final readonly class TotpEnrollmentResult
{
    public function __construct(
        private TotpFactorId $factorId,
        private TotpSecret $secret,
        private TotpParameters $parameters
    ) {
    }

    public function factorId(): TotpFactorId
    {
        return $this->factorId;
    }

    public function secret(): TotpSecret
    {
        return $this->secret;
    }

    public function parameters(): TotpParameters
    {
        return $this->parameters;
    }
}
