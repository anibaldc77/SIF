<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Authentication\AuthenticationRequest;
use Sif\Foundation\Security\Authentication\AuthenticatorId;
use Throwable;

interface AuthenticationTechnicalFailureHandlerInterface
{
    public function handle(
        AuthenticationRequest $request,
        AuthenticatorId $authenticatorId,
        Throwable $failure
    ): void;
}
