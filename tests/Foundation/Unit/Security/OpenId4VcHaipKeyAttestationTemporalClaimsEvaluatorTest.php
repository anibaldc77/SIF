<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Contracts\ClockInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationTemporalClaimsEvaluator;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationTrustPolicy;
use Sif\Foundation\Tests\Fixtures\Context\FrozenClock;

final class OpenId4VcHaipKeyAttestationTemporalClaimsEvaluatorTest extends TestCase
{
    private const NOW = 1700000000;

    public function testAcceptsValidJwtAndAttestationTemporalClaims(): void
    {
        $evaluator = self::evaluator();

        self::assertTrue($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 60, 'exp' => self::NOW + 60],
            'jwt',
            self::policy()
        ));

        self::assertTrue($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 60],
            'attestation',
            self::policy()
        ));
    }

    public function testRejectsMissingOrNonIntegerIat(): void
    {
        $evaluator = self::evaluator();
        $policy = self::policy();

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['exp' => self::NOW + 60],
            'jwt',
            $policy
        ));

        foreach (['1700000000', 1.5, true, null] as $iat) {
            self::assertFalse($evaluator->isTemporallyAdmissible(
                ['iat' => $iat, 'exp' => self::NOW + 60],
                'jwt',
                $policy
            ));
        }
    }

    public function testRequiresExpForJwt(): void
    {
        self::assertFalse(self::evaluator()->isTemporallyAdmissible(
            ['iat' => self::NOW - 1],
            'jwt',
            self::policy()
        ));
    }

    public function testRejectsMalformedExpWhenPresent(): void
    {
        $evaluator = self::evaluator();

        foreach (['1700000060', 1.5, true, null] as $exp) {
            self::assertFalse($evaluator->isTemporallyAdmissible(
                ['iat' => self::NOW - 60, 'exp' => $exp],
                'attestation',
                self::policy()
            ));
        }
    }

    public function testExpirationIsExclusiveWithoutClockSkew(): void
    {
        $evaluator = self::evaluator();

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 60, 'exp' => self::NOW],
            'jwt',
            self::policy()
        ));

        self::assertTrue($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 60, 'exp' => self::NOW + 1],
            'jwt',
            self::policy()
        ));
    }

    public function testExpirationSkewBoundaryIsExclusive(): void
    {
        $evaluator = self::evaluator();
        $policy = self::policy(300, 5);

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 60, 'exp' => self::NOW - 5],
            'jwt',
            $policy
        ));

        self::assertTrue($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 60, 'exp' => self::NOW - 4],
            'jwt',
            $policy
        ));
    }

    public function testRejectsExpirationNotAfterIssueTime(): void
    {
        $evaluator = self::evaluator();

        foreach ([self::NOW - 60, self::NOW - 61] as $exp) {
            self::assertFalse($evaluator->isTemporallyAdmissible(
                ['iat' => self::NOW - 60, 'exp' => $exp],
                'jwt',
                self::policy()
            ));
        }
    }

    public function testMaximumAgeBoundaryIsInclusive(): void
    {
        $evaluator = self::evaluator();
        $policy = self::policy(300, 5);

        self::assertTrue($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 305],
            'attestation',
            $policy
        ));

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 306],
            'attestation',
            $policy
        ));
    }

    public function testFutureIssueTimeBoundaryIsInclusive(): void
    {
        $evaluator = self::evaluator();
        $policy = self::policy(300, 5);

        self::assertTrue($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW + 5, 'exp' => self::NOW + 60],
            'jwt',
            $policy
        ));

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW + 6, 'exp' => self::NOW + 60],
            'jwt',
            $policy
        ));
    }

    public function testOptionalNotBeforeIsEvaluated(): void
    {
        $evaluator = self::evaluator();
        $policy = self::policy(300, 5);

        self::assertTrue($evaluator->isTemporallyAdmissible(
            [
                'iat' => self::NOW - 60,
                'exp' => self::NOW + 60,
                'nbf' => self::NOW + 5,
            ],
            'jwt',
            $policy
        ));

        self::assertFalse($evaluator->isTemporallyAdmissible(
            [
                'iat' => self::NOW - 60,
                'exp' => self::NOW + 60,
                'nbf' => self::NOW + 6,
            ],
            'jwt',
            $policy
        ));
    }

    public function testRejectsMalformedOrInconsistentNotBefore(): void
    {
        $evaluator = self::evaluator();

        foreach (['invalid', 1.5, true, null] as $nbf) {
            self::assertFalse($evaluator->isTemporallyAdmissible(
                [
                    'iat' => self::NOW - 60,
                    'exp' => self::NOW + 60,
                    'nbf' => $nbf,
                ],
                'jwt',
                self::policy()
            ));
        }

        self::assertFalse($evaluator->isTemporallyAdmissible(
            [
                'iat' => self::NOW - 60,
                'exp' => self::NOW + 60,
                'nbf' => self::NOW + 60,
            ],
            'jwt',
            self::policy()
        ));
    }

    public function testRejectsUnknownProofTypes(): void
    {
        $evaluator = self::evaluator();

        foreach (['', 'Jwt', 'unknown', 'key_attestation'] as $type) {
            self::assertFalse($evaluator->isTemporallyAdmissible(
                ['iat' => self::NOW - 60, 'exp' => self::NOW + 60],
                $type,
                self::policy()
            ));
        }
    }

    public function testDifferentTimeZonesRepresentSameInstant(): void
    {
        $utc = new DateTimeImmutable('@' . self::NOW);
        $mendoza = $utc->setTimezone(new DateTimeZone('-03:00'));

        $claims = [
            'iat' => self::NOW - 60,
            'exp' => self::NOW + 60,
        ];

        $utcEvaluator = new OpenId4VcHaipKeyAttestationTemporalClaimsEvaluator(
            new FrozenClock($utc)
        );

        $localEvaluator = new OpenId4VcHaipKeyAttestationTemporalClaimsEvaluator(
            new FrozenClock($mendoza)
        );

        self::assertSame(
            $utcEvaluator->isTemporallyAdmissible($claims, 'jwt', self::policy()),
            $localEvaluator->isTemporallyAdmissible($claims, 'jwt', self::policy())
        );
    }

    public function testClockFailureIsFailClosed(): void
    {
        $clock = new class implements ClockInterface {
            public function now(): DateTimeImmutable
            {
                throw new RuntimeException('Test clock unavailable.');
            }
        };

        $evaluator = new OpenId4VcHaipKeyAttestationTemporalClaimsEvaluator(
            $clock
        );

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 60, 'exp' => self::NOW + 60],
            'jwt',
            self::policy()
        ));
    }

    public function testClockIsReadExactlyOncePerEvaluation(): void
    {
        $clock = new class implements ClockInterface {
            public int $calls = 0;

            public function now(): DateTimeImmutable
            {
                $this->calls++;

                return new DateTimeImmutable('@1700000000');
            }
        };

        $evaluator = new OpenId4VcHaipKeyAttestationTemporalClaimsEvaluator(
            $clock
        );

        self::assertTrue($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 60, 'exp' => self::NOW + 60],
            'jwt',
            self::policy()
        ));

        self::assertSame(1, $clock->calls);
    }

    public function testExtremeClaimIntegersDoNotOverflow(): void
    {
        $evaluator = self::evaluator();

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['iat' => PHP_INT_MIN, 'exp' => PHP_INT_MAX],
            'jwt',
            self::policy(300, 5)
        ));

        self::assertTrue($evaluator->isTemporallyAdmissible(
            [
                'iat' => self::NOW - 60,
                'exp' => PHP_INT_MAX,
                'nbf' => PHP_INT_MIN,
            ],
            'jwt',
            self::policy(300, 5)
        ));
    }

    private static function evaluator(): OpenId4VcHaipKeyAttestationTemporalClaimsEvaluator
    {
        return new OpenId4VcHaipKeyAttestationTemporalClaimsEvaluator(
            new FrozenClock(new DateTimeImmutable('@' . self::NOW))
        );
    }

    private static function policy(
        int $maximumAge = 300,
        int $clockSkew = 0
    ): OpenId4VcHaipKeyAttestationTrustPolicy {
        // Structural test fixture only; NOT a real trusted certificate.
        $pem = "-----BEGIN CERTIFICATE-----\n"
            . base64_encode('sif-test-anchor')
            . "\n-----END CERTIFICATE-----\n";

        return new OpenId4VcHaipKeyAttestationTrustPolicy(
            'https://credential.example',
            [$pem],
            ['https://wallet.example'],
            ['ES256'],
            [],
            false,
            $maximumAge,
            $clockSkew
        );
    }
}
