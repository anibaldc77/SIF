<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\MultiFactor\Totp\TotpSecret;

interface TotpSecretGeneratorInterface
{
    public function generate(): TotpSecret;
}
