<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authentication;

use Sif\Foundation\Security\Contracts\AuthenticationTechnicalFailureHandlerInterface;
use Sif\Foundation\Security\Results\AuthenticationFailureReason;
use Sif\Foundation\Security\Results\AuthenticationResult;
use Throwable;

final readonly class AuthenticationOrchestrator
{
    private AuthenticationTechnicalFailureHandlerInterface $technicalFailureHandler;

    public function __construct(
        private AuthenticatorRegistry $registry,
        ?AuthenticationTechnicalFailureHandlerInterface $technicalFailureHandler = null
    ) {
        $this->technicalFailureHandler = $technicalFailureHandler ?? new NullAuthenticationTechnicalFailureHandler();
    }

    public function authenticate(AuthenticationRequest $request): AuthenticationResult
    {
        $authenticator = $this->registry->findFor($request->credential()->type());

        if ($authenticator === null) {
            return AuthenticationResult::failed(AuthenticationFailureReason::UnsupportedCredentials);
        }

        try {
            return $authenticator->authenticate($request);
        } catch (Throwable $failure) {
            $this->technicalFailureHandler->handle($request, $authenticator->id(), $failure);

            return AuthenticationResult::failed(AuthenticationFailureReason::InfrastructureFailure);
        }
    }
}
