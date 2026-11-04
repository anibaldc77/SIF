<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use Sif\Foundation\Contracts\ClockInterface;
use Throwable;

/**
 * Evaluates temporal admissibility only.
 *
 * This component does NOT authenticate claims, proof types, certificates
 * or key attestation evidence. Its positive result must never be treated
 * as an integral Key Attestation verification result.
 */
final readonly class OpenId4VcHaipKeyAttestationTemporalClaimsEvaluator
{
    public function __construct(
        private ClockInterface $clock
    ) {
    }

    /**
     * The proof type and policy must be obtained from trusted composition.
     * This evaluator cannot establish their provenance.
     *
     * @param array<string, mixed> $claims
     */
    public function isTemporallyAdmissible(
        array $claims,
        string $proofType,
        OpenId4VcHaipKeyAttestationTrustPolicy $policy
    ): bool {
        if (!in_array($proofType, ['jwt', 'attestation'], true)) {
            return false;
        }

        $issuedAt = $claims['iat'] ?? null;

        if (!is_int($issuedAt)) {
            return false;
        }

        $hasExpiration = array_key_exists('exp', $claims);
        $expiresAt = $claims['exp'] ?? null;

        if ($hasExpiration && !is_int($expiresAt)) {
            return false;
        }

        if ($proofType === 'jwt' && !$hasExpiration) {
            return false;
        }

        $hasNotBefore = array_key_exists('nbf', $claims);
        $notBefore = $claims['nbf'] ?? null;

        if ($hasNotBefore && !is_int($notBefore)) {
            return false;
        }

        // Explicit SIF profile restrictions.
        if ($expiresAt !== null && $expiresAt <= $issuedAt) {
            return false;
        }

        if (
            $expiresAt !== null
            && $notBefore !== null
            && $notBefore >= $expiresAt
        ) {
            return false;
        }

        try {
            // One clock reading per evaluation.
            $now = $this->clock->now()->getTimestamp();
        } catch (Throwable) {
            return false;
        }

        $skew = $policy->allowedClockSkewSeconds();
        $maximumAge = $policy->maximumAttestationAgeSeconds();

        // Saturated arithmetic avoids overflow near PHP_INT_MAX.
        $latestAllowedTime = $now > PHP_INT_MAX - $skew
            ? PHP_INT_MAX
            : $now + $skew;

        if ($issuedAt > $latestAllowedTime) {
            return false;
        }

        $ageWindow = $maximumAge + $skew;

        // Saturated arithmetic avoids overflow near PHP_INT_MIN.
        $earliestAllowedIssue = $now < PHP_INT_MIN + $ageWindow
            ? PHP_INT_MIN
            : $now - $ageWindow;

        if ($issuedAt < $earliestAllowedIssue) {
            return false;
        }

        if (
            $notBefore !== null
            && $notBefore > $latestAllowedTime
        ) {
            return false;
        }

        if ($expiresAt !== null) {
            // If exp + skew exceeds PHP_INT_MAX, every representable
            // current timestamp is earlier than that effective limit.
            if (
                $expiresAt <= PHP_INT_MAX - $skew
                && $now >= $expiresAt + $skew
            ) {
                return false;
            }
        }

        return true;
    }
}
