<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\PersistentAuthentication;

use DateTimeImmutable;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\PersistentAuthentication\PersistentSessionRestorationResult;
use Sif\Foundation\Security\PersistentAuthentication\PersistentSessionRestorationService;
use Sif\Foundation\Session\SessionState;

final readonly class PersistentAuthenticationHttpRestorationService
{
    public function __construct(
        private PersistentSessionRestorationService $restoration
    ) {
    }

    public function restoreFromCookie(
        string $cookieValue,
        SessionState $session,
        SecurityContext $securityContext,
        DateTimeImmutable $now
    ): PersistentSessionRestorationResult {
        $token = PersistentAuthenticationCookiePayload::parse($cookieValue);

        return $this->restoration->restore(
            $token,
            $session,
            $securityContext,
            $now
        );
    }

    public function replacementCookie(
        PersistentSessionRestorationResult $result
    ): ?PersistentAuthenticationCookiePayload {
        $replacement = $result->replacementToken();

        return $replacement === null
            ? null
            : PersistentAuthenticationCookiePayload::fromToken($replacement);
    }
}
