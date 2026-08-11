<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialStatusFreshnessPolicyInterface;
use Sif\Foundation\Security\Exceptions\CredentialStatusEnforcementException;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Status\Verifier\HighAssuranceCredentialStatusEnforcer;

final class CredentialStatusHighAssuranceEnforcementTest extends TestCase
{
    public function testValidAssessmentIsAccepted(): void
    {
        $policy = new class implements CredentialStatusFreshnessPolicyInterface {
            public function validate(
                CredentialStatusAssessment $assessment
            ): void {
            }
        };

        $enforcer = new HighAssuranceCredentialStatusEnforcer($policy);

        $assessment = $this->assessment(
            valid: true,
            revoked: false,
            suspended: false
        );

        $decision = $enforcer->enforce($assessment);

        self::assertTrue($decision->accepted());
        self::assertSame($assessment, $decision->assessment());
    }

    public function testRevokedCredentialIsRejected(): void
    {
        $enforcer = new HighAssuranceCredentialStatusEnforcer(
            $this->permissiveFreshnessPolicy()
        );

        $this->expectException(
            CredentialStatusEnforcementException::class
        );

        $enforcer->enforce(
            $this->assessment(
                valid: true,
                revoked: true,
                suspended: false
            )
        );
    }

    public function testSuspendedCredentialIsRejected(): void
    {
        $enforcer = new HighAssuranceCredentialStatusEnforcer(
            $this->permissiveFreshnessPolicy()
        );

        $this->expectException(
            CredentialStatusEnforcementException::class
        );

        $enforcer->enforce(
            $this->assessment(
                valid: true,
                revoked: false,
                suspended: true
            )
        );
    }

    public function testPolicyViolationsAreRejected(): void
    {
        $enforcer = new HighAssuranceCredentialStatusEnforcer(
            $this->permissiveFreshnessPolicy()
        );

        $this->expectException(
            CredentialStatusEnforcementException::class
        );

        $enforcer->enforce(
            $this->assessment(
                valid: true,
                revoked: false,
                suspended: false,
                violations: ['status-profile-violation']
            )
        );
    }

    public function testInvalidAssessmentIsRejected(): void
    {
        $enforcer = new HighAssuranceCredentialStatusEnforcer(
            $this->permissiveFreshnessPolicy()
        );

        $this->expectException(
            CredentialStatusEnforcementException::class
        );

        $enforcer->enforce(
            $this->assessment(
                valid: false,
                revoked: false,
                suspended: false
            )
        );
    }

    public function testFreshnessPolicyIsAlwaysAppliedBeforeAcceptance(): void
    {
        $policy = new class implements CredentialStatusFreshnessPolicyInterface {
            public function validate(
                CredentialStatusAssessment $assessment
            ): void {
                throw new \RuntimeException('Stale status evidence.');
            }
        };

        $enforcer = new HighAssuranceCredentialStatusEnforcer($policy);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stale status evidence.');

        $enforcer->enforce(
            $this->assessment(
                valid: true,
                revoked: false,
                suspended: false
            )
        );
    }

    public function testEnforcementLayerRemainsInfrastructureNeutral(): void
    {
        $reflection = new \ReflectionClass(
            HighAssuranceCredentialStatusEnforcer::class
        );

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('Redis', $source);
        self::assertStringNotContainsString('PDO', $source);
        self::assertStringNotContainsString('Guzzle', $source);
        self::assertStringNotContainsString(
            'curl_',
            strtolower($source)
        );
        self::assertStringNotContainsString(
            'sleep(',
            strtolower($source)
        );
    }

    /**
     * @param list<string> $violations
     */
    private function assessment(
        bool $valid,
        bool $revoked,
        bool $suspended,
        array $violations = []
    ): CredentialStatusAssessment {
        return new CredentialStatusAssessment(
            $valid,
            $revoked,
            $suspended,
            new DateTimeImmutable('2026-08-11T12:00:00Z'),
            new DateTimeImmutable('2026-08-11T11:59:00Z'),
            $violations
        );
    }

    private function permissiveFreshnessPolicy(): CredentialStatusFreshnessPolicyInterface
    {
        return new class implements CredentialStatusFreshnessPolicyInterface {
            public function validate(
                CredentialStatusAssessment $assessment
            ): void {
            }
        };
    }
}