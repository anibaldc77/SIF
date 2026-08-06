<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\Password;

use DateTimeImmutable;
use Sif\Foundation\Security\Authentication\AuthenticationOrchestrator;
use Sif\Foundation\Security\Authentication\AuthenticationRequest;
use Sif\Foundation\Security\Authentication\AuthenticationRequestId;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Password\Authentication\PasswordAuthenticationCredential;
use Sif\Foundation\Security\Results\AuthenticationResult;
use Sif\Foundation\Security\Session\SessionAuthenticationManager;
use Sif\Foundation\Session\SessionState;

final readonly class PasswordSessionLoginService
{
    public function __construct(
        private AuthenticationOrchestrator $orchestrator,
        private SessionAuthenticationManager $sessionAuthentication,
        private SecurityContext $securityContext
    ) {
    }

    public function login(
        PasswordLoginRequest $login,
        AuthenticationRequestId $requestId,
        DateTimeImmutable $requestedAt,
        SessionState $session
    ): AuthenticationResult {
        $result = $this->orchestrator->authenticate(new AuthenticationRequest(
            $requestId,
            new PasswordAuthenticationCredential($login->lookupKey(), $login->password()),
            $requestedAt
        ));

        $principal = $result->principal();
        if ($principal !== null) {
            $this->sessionAuthentication->authenticate($principal, $session, $this->securityContext);
        }

        return $result;
    }

    public function logout(SessionState $session): void
    {
        $this->sessionAuthentication->logout($session, $this->securityContext);
    }
}
