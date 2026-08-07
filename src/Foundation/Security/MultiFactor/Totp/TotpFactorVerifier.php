<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\TotpFactorStoreInterface;
use Sif\Foundation\Security\Contracts\TotpVerifierInterface;
use Sif\Foundation\Security\Identity\IdentityId;

final readonly class TotpFactorVerifier
{
    public function __construct(
        private TotpVerifierInterface $verifier,
        private TotpFactorStoreInterface $store
    ) {
    }

    public function verify(
        IdentityId $identityId,
        TotpCode $code,
        DateTimeImmutable $at
    ): TotpFactorVerificationResult {
        $factor = $this->store->findActiveForIdentity($identityId);

        if ($factor === null) {
            return TotpFactorVerificationResult::rejected();
        }

        $verification = $this->verifier->verify(
            $factor->secret(),
            $code,
            $factor->parameters(),
            $at
        );
        $counter = $verification->matchedCounter();

        if (!$verification->isVerified() || $counter === null) {
            return TotpFactorVerificationResult::rejected();
        }

        if (!$this->store->acceptCounter($factor->id(), $counter)) {
            return TotpFactorVerificationResult::rejected();
        }

        return TotpFactorVerificationResult::verified();
    }
}
