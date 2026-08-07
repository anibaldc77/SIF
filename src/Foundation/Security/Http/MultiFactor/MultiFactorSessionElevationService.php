<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\MultiFactor;

use DateTimeImmutable;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\MultiFactor\MultiFactorSatisfactionResult;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\RecoveryCodeMultiFactorService;
use Sif\Foundation\Security\MultiFactor\Totp\TotpMultiFactorService;
use Sif\Foundation\Security\Session\SessionAuthenticationManager;
use Sif\Foundation\Session\SessionState;

final readonly class MultiFactorSessionElevationService
{
    public function __construct(
        private TotpMultiFactorService $totpService,
        private RecoveryCodeMultiFactorService $recoveryCodeService,
        private SessionAuthenticationManager $sessionAuthentication,
        private SecurityContext $securityContext
    ) {
    }

    public function satisfyTotp(
        TotpChallengeResponsePayload $payload,
        SessionState $session,
        DateTimeImmutable $now
    ): MultiFactorSatisfactionResult {
        $principal = $this->authenticatedPrincipal();
        if ($principal === null) {
            return MultiFactorSatisfactionResult::rejected();
        }

        return $this->persistIfSatisfied(
            $this->totpService->satisfy(
                $principal,
                $payload->challengeId(),
                $payload->code(),
                $now
            ),
            $session
        );
    }

    public function satisfyRecoveryCode(
        RecoveryCodeChallengeResponsePayload $payload,
        SessionState $session,
        DateTimeImmutable $now
    ): MultiFactorSatisfactionResult {
        $principal = $this->authenticatedPrincipal();
        if ($principal === null) {
            return MultiFactorSatisfactionResult::rejected();
        }

        return $this->persistIfSatisfied(
            $this->recoveryCodeService->satisfy(
                $principal,
                $payload->challengeId(),
                $payload->code(),
                $now
            ),
            $session
        );
    }

    private function authenticatedPrincipal(): ?AuthenticatedPrincipal
    {
        $principal = $this->securityContext->principal();

        return $principal instanceof AuthenticatedPrincipal ? $principal : null;
    }

    private function persistIfSatisfied(
        MultiFactorSatisfactionResult $result,
        SessionState $session
    ): MultiFactorSatisfactionResult {
        $principal = $result->principal();
        if ($principal !== null) {
            $this->sessionAuthentication->authenticate(
                $principal,
                $session,
                $this->securityContext
            );
        }

        return $result;
    }
}
