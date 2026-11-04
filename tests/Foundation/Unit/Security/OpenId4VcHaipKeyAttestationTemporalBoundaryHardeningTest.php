<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use Error;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\ClockInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationTemporalClaimsEvaluator;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationTrustPolicy;
use Sif\Foundation\Tests\Fixtures\Context\FrozenClock;
use stdClass;

/**
 * C4.5C1B: supplemental arithmetic and fail-closed boundary tests.
 *
 * Positive outcomes establish temporal admissibility only.
 * These tests do not authenticate any cryptographic evidence.
 */
final class OpenId4VcHaipKeyAttestationTemporalBoundaryHardeningTest extends TestCase
{
    private const NOW = 1700000000;

    public function testIssueTimeAtMaximumIntegerDoesNotOverflow(): void
    {
        $evaluator = self::at(PHP_INT_MAX - 1);
        $policy = self::policy(300, 5);

        self::assertTrue($evaluator->isTemporallyAdmissible(
            ['iat' => PHP_INT_MAX],
            'attestation',
            $policy
        ));

        self::assertTrue($evaluator->isTemporallyAdmissible(
            ['iat' => PHP_INT_MAX - 306],
            'attestation',
            $policy
        ));

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['iat' => PHP_INT_MAX - 307],
            'attestation',
            $policy
        ));
    }

    public function testIssueTimeAtMinimumIntegerDoesNotUnderflow(): void
    {
        $evaluator = self::at(PHP_INT_MIN + 1);
        $policy = self::policy(300, 5);

        self::assertTrue($evaluator->isTemporallyAdmissible(
            ['iat' => PHP_INT_MIN],
            'attestation',
            $policy
        ));

        self::assertTrue($evaluator->isTemporallyAdmissible(
            ['iat' => PHP_INT_MIN + 6],
            'attestation',
            $policy
        ));

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['iat' => PHP_INT_MIN + 7],
            'attestation',
            $policy
        ));
    }

    public function testExpirationAtMaximumIntegerWithSkewIsSafe(): void
    {
        $evaluator = self::at(PHP_INT_MAX);
        $claims = [
            'iat' => PHP_INT_MAX - 60,
            'exp' => PHP_INT_MAX - 1,
        ];

        self::assertTrue($evaluator->isTemporallyAdmissible(
            $claims,
            'jwt',
            self::policy(300, 5)
        ));

        self::assertFalse($evaluator->isTemporallyAdmissible(
            $claims,
            'jwt',
            self::policy(300, 0)
        ));

        $claims['exp'] = PHP_INT_MAX;

        self::assertFalse($evaluator->isTemporallyAdmissible(
            $claims,
            'jwt',
            self::policy(300, 0)
        ));

        self::assertTrue($evaluator->isTemporallyAdmissible(
            $claims,
            'jwt',
            self::policy(300, 5)
        ));
    }

    public function testExpirationNearMinimumIntegerIsExclusive(): void
    {
        $claims = [
            'iat' => PHP_INT_MIN,
            'exp' => PHP_INT_MIN + 1,
        ];

        self::assertTrue(
            self::at(PHP_INT_MIN + 5)->isTemporallyAdmissible(
                $claims,
                'jwt',
                self::policy(300, 5)
            )
        );

        self::assertFalse(
            self::at(PHP_INT_MIN + 6)->isTemporallyAdmissible(
                $claims,
                'jwt',
                self::policy(300, 5)
            )
        );
    }

    public function testLargestConfiguredPolicyWindowsAreBounded(): void
    {
        $evaluator = self::at(self::NOW);
        $policy = self::policy(86400, 300);

        self::assertTrue($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 86700],
            'attestation',
            $policy
        ));

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 86701],
            'attestation',
            $policy
        ));

        self::assertTrue($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW + 300],
            'attestation',
            $policy
        ));

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW + 301],
            'attestation',
            $policy
        ));

        self::assertTrue($evaluator->isTemporallyAdmissible(
            [
                'iat' => self::NOW - 300,
                'exp' => self::NOW - 299,
            ],
            'jwt',
            $policy
        ));

        self::assertFalse($evaluator->isTemporallyAdmissible(
            [
                'iat' => self::NOW - 301,
                'exp' => self::NOW - 300,
            ],
            'jwt',
            $policy
        ));
    }

    public function testTemporalAdmissibilityChangesWithTrustedPolicy(): void
    {
        $evaluator = self::at(self::NOW);
        $claims = ['iat' => self::NOW - 200];

        self::assertFalse($evaluator->isTemporallyAdmissible(
            $claims,
            'attestation',
            self::policy(100, 0)
        ));

        self::assertTrue($evaluator->isTemporallyAdmissible(
            $claims,
            'attestation',
            self::policy(300, 0)
        ));
    }

    public function testRejectsNestedValuesInTemporalClaims(): void
    {
        $evaluator = self::at(self::NOW);
        $policy = self::policy();

        $invalidClaims = [
            ['iat' => []],
            ['iat' => new stdClass()],
            ['iat' => self::NOW - 60, 'exp' => []],
            ['iat' => self::NOW - 60, 'exp' => new stdClass()],
            ['iat' => self::NOW - 60, 'nbf' => []],
            ['iat' => self::NOW - 60, 'nbf' => new stdClass()],
        ];

        foreach ($invalidClaims as $claims) {
            self::assertFalse($evaluator->isTemporallyAdmissible(
                $claims,
                'attestation',
                $policy
            ));
        }
    }

    public function testClockErrorAlsoFailsClosed(): void
    {
        $clock = new class implements ClockInterface {
            public function now(): DateTimeImmutable
            {
                throw new Error('Test clock error.');
            }
        };

        $evaluator = new OpenId4VcHaipKeyAttestationTemporalClaimsEvaluator(
            $clock
        );

        self::assertFalse($evaluator->isTemporallyAdmissible(
            [
                'iat' => self::NOW - 60,
                'exp' => self::NOW + 60,
            ],
            'jwt',
            self::policy()
        ));
    }

    public function testInvalidInputDoesNotConsultClock(): void
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

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['iat' => 'invalid'],
            'attestation',
            self::policy()
        ));

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 60],
            'jwt',
            self::policy()
        ));

        self::assertFalse($evaluator->isTemporallyAdmissible(
            ['iat' => self::NOW - 60],
            'unknown',
            self::policy()
        ));

        self::assertSame(0, $clock->calls);
    }

    private static function at(
        int $timestamp
    ): OpenId4VcHaipKeyAttestationTemporalClaimsEvaluator {
        return new OpenId4VcHaipKeyAttestationTemporalClaimsEvaluator(
            new FrozenClock(new DateTimeImmutable('@' . $timestamp))
        );
    }

    private static function policy(
        int $maximumAge = 300,
        int $skew = 0
    ): OpenId4VcHaipKeyAttestationTrustPolicy {
        // Structural fixture only. Not a trusted X.509 certificate.
        $pem = "-----BEGIN CERTIFICATE-----\n"
            . base64_encode('sif-temporal-test-anchor')
            . "\n-----END CERTIFICATE-----\n";

        return new OpenId4VcHaipKeyAttestationTrustPolicy(
            'https://credential.example',
            [$pem],
            ['https://attestation-provider.example'],
            ['ES256'],
            [],
            false,
            $maximumAge,
            $skew
        );
    }
}
