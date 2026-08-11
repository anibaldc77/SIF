<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Status\Token\TokenStatusList;

interface TokenStatusListAuthenticityVerifierInterface
{
    public function verify(
        TokenStatusList $statusList
    ): void;
}
