<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\TotpFactorStoreInterface;
use Sif\Foundation\Security\Contracts\TotpSecretGeneratorInterface;
use Sif\Foundation\Security\Contracts\TotpVerifierInterface;
use Sif\Foundation\Security\Identity\IdentityId;

final readonly class TotpEnrollmentService
{
    public function __construct(
        private TotpSecretGeneratorInterface $secretGenerator,
        private TotpVerifierInterface $verifier,
        private TotpFactorStoreInterface $store,
        private TotpParameters $parameters = new TotpParameters(new TotpHashAlgorithm('sha1'))
    ) {
    }

    public function begin(
        TotpFactorId $factorId,
        IdentityId $identityId,
        DateTimeImmutable $enrolledAt
    ): TotpEnrollmentResult {
        $secret = $this->secretGenerator->generate();
        $factor = TotpFactorRecord::pending(
            $factorId,
            $identityId,
            $secret,
            $this->parameters,
            $enrolledAt
        );

        $this->store->save($factor);

        return new TotpEnrollmentResult($factorId, $secret, $this->parameters);
    }

    public function activate(
        TotpFactorId $factorId,
        TotpCode $code,
        DateTimeImmutable $at
    ): TotpFactorVerificationResult {
        $factor = $this->store->find($factorId);

        if ($factor === null || $factor->status() !== TotpFactorStatus::Pending) {
            return TotpFactorVerificationResult::rejected();
        }

        $verification = $this->verifier->verify(
            $factor->secret(),
            $code,
            $factor->parameters(),
            $at
        );
        $counter = $verification->matchedCounter();

        if (!$verification->isVerified() || $counter === null || !$this->store->activate($factorId, $counter)) {
            return TotpFactorVerificationResult::rejected();
        }

        return TotpFactorVerificationResult::verified();
    }
}
