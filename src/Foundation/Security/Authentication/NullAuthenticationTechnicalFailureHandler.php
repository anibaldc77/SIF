<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authentication;

use Sif\Foundation\Security\Contracts\AuthenticationTechnicalFailureHandlerInterface;
use Throwable;

final class NullAuthenticationTechnicalFailureHandler implements AuthenticationTechnicalFailureHandlerInterface
{
    public function handle(
        AuthenticationRequest $request,
        AuthenticatorId $authenticatorId,
        Throwable $failure
    ): void {
    }
}
