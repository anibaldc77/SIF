<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Csrf;

use Sif\Foundation\Session\SessionState;

final readonly class CsrfTokenManager
{
    public function __construct(
        private CsrfTokenGenerator $generator = new CsrfTokenGenerator(),
        private CsrfConfiguration $configuration = new CsrfConfiguration(),
    ) {
    }

    public function token(SessionState $session): CsrfToken
    {
        $stored = $session->get($this->configuration->sessionKey());
        if (is_string($stored)) {
            try {
                return new CsrfToken($stored);
            } catch (\Throwable) {
                // Replace malformed internal state with a new opaque token.
            }
        }

        $token = $this->generator->generate();
        $session->put($this->configuration->sessionKey(), $token->value());
        return $token;
    }

    public function expected(SessionState $session): ?CsrfToken
    {
        $stored = $session->get($this->configuration->sessionKey());
        if (!is_string($stored)) {
            return null;
        }

        try {
            return new CsrfToken($stored);
        } catch (\Throwable) {
            return null;
        }
    }

    public function validate(SessionState $session, ?string $submitted): CsrfValidationResult
    {
        $expected = $this->expected($session);
        if ($expected === null) {
            return CsrfValidationResult::invalid(CsrfFailureReason::MissingExpectedToken);
        }
        if ($submitted === null || $submitted === '') {
            return CsrfValidationResult::invalid(CsrfFailureReason::MissingSubmittedToken);
        }
        if (strlen($submitted) > 256 || preg_match('/[\r\n\x00]/', $submitted) === 1) {
            return CsrfValidationResult::invalid(CsrfFailureReason::InvalidSubmittedToken);
        }

        return hash_equals($expected->value(), $submitted)
            ? CsrfValidationResult::valid()
            : CsrfValidationResult::invalid(CsrfFailureReason::InvalidSubmittedToken);
    }
}
