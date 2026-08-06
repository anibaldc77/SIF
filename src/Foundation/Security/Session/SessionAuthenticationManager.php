<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Session;

use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Session\SessionState;
use Throwable;

final readonly class SessionAuthenticationManager
{
    public const SESSION_KEY = '_sif_security_principal';

    public function restore(SessionState $session, SecurityContext $context): void
    {
        $payload = $session->get(self::SESSION_KEY);
        if (!is_array($payload)) {
            $context->clear();
            return;
        }

        try {
            /** @var array<string, mixed> $payload */
            $context->replace(SessionPrincipalSnapshot::fromArray($payload)->toPrincipal());
        } catch (Throwable) {
            $session->remove(self::SESSION_KEY);
            $context->clear();
        }
    }

    public function authenticate(
        AuthenticatedPrincipal $principal,
        SessionState $session,
        SecurityContext $context,
    ): void {
        $session->put(self::SESSION_KEY, SessionPrincipalSnapshot::fromPrincipal($principal)->toArray());
        $session->requestRegeneration();
        $context->replace($principal);
    }

    public function logout(SessionState $session, SecurityContext $context): void
    {
        $session->remove(self::SESSION_KEY);
        $session->requestRegeneration();
        $context->clear();
    }
}
