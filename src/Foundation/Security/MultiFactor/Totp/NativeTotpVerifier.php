<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\TotpVerifierInterface;
use Sif\Foundation\Security\Exceptions\InvalidTotpConfigurationException;

final readonly class NativeTotpVerifier implements TotpVerifierInterface
{
    public function __construct(private NativeTotpCodeGenerator $codeGenerator = new NativeTotpCodeGenerator())
    {
    }

    public function verify(
        TotpSecret $secret,
        TotpCode $code,
        TotpParameters $parameters,
        DateTimeImmutable $at
    ): TotpVerificationResult {
        if ($code->digits() !== $parameters->digits()) {
            return TotpVerificationResult::rejected();
        }

        $timestamp = $at->getTimestamp();

        if ($timestamp < 0) {
            throw new InvalidTotpConfigurationException('TOTP timestamps before the Unix epoch are not supported.');
        }

        $currentCounter = intdiv($timestamp, $parameters->periodSeconds());

        for ($offset = -$parameters->allowedPastWindows(); $offset <= $parameters->allowedFutureWindows(); $offset++) {
            $candidateCounter = $currentCounter + $offset;

            if ($candidateCounter < 0) {
                continue;
            }

            $candidate = $this->codeGenerator->generateForCounter($secret, $parameters, $candidateCounter);
            $matches = $candidate->expose(
                static fn (string $candidateValue): bool => $code->expose(
                    static fn (string $providedValue): bool => hash_equals($candidateValue, $providedValue)
                )
            );

            if ($matches) {
                return TotpVerificationResult::verified($candidateCounter);
            }
        }

        return TotpVerificationResult::rejected();
    }
}
