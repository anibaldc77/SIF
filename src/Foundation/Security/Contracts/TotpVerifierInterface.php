<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\MultiFactor\Totp\TotpCode;
use Sif\Foundation\Security\MultiFactor\Totp\TotpParameters;
use Sif\Foundation\Security\MultiFactor\Totp\TotpSecret;
use Sif\Foundation\Security\MultiFactor\Totp\TotpVerificationResult;

interface TotpVerifierInterface
{
    public function verify(
        TotpSecret $secret,
        TotpCode $code,
        TotpParameters $parameters,
        DateTimeImmutable $at
    ): TotpVerificationResult;
}
