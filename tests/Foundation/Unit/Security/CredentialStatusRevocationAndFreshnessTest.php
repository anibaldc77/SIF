<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialFreshnessPolicyInterface;
use Sif\Foundation\Security\Contracts\CredentialStatusPolicyInterface;
use Sif\Foundation\Security\Contracts\CredentialStatusResolverInterface;
use Sif\Foundation\Security\VerifiableCredentials\CredentialStatus;
use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusAssessment;
use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusContext;
use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusEvidence;

final class CredentialStatusRevocationAndFreshnessTest extends TestCase
{
    public function testCredentialStatusSupportsLifecycleStates(): void
    {
        self::assertSame(
            'valid',
            (new CredentialStatus(CredentialStatus::VALID))->value()
        );
        self::assertSame(
            'revoked',
            (new CredentialStatus(CredentialStatus::REVOKED))->value()
        );
    }

    public function testStatusContextKeepsFreshnessRequirementsExplicit(): void
    {
        $context = new CredentialStatusContext(
            new DateTimeImmutable('2026-08-10T13:45:00Z'),
            true,
            true,
            120
        );

        self::assertTrue($context->requireStatusCheck());
        self::assertTrue($context->requireFreshStatus());
        self::assertSame(120, $context->maximumStatusAgeSeconds());
    }

    public function testStatusEvidenceKeepsStateSourceAndCheckTimeExplicit(): void
    {
        $evidence = new CredentialStatusEvidence(
            new CredentialStatus(CredentialStatus::VALID),
            new DateTimeImmutable('2026-08-10T13:44:30Z'),
            'status-resolver'
        );

        self::assertSame('valid', $evidence->status()->value());
        self::assertSame('status-resolver', $evidence->source());
    }

    public function testStatusAssessmentSeparatesViolationsAndWarnings(): void
    {
        $evidence = new CredentialStatusEvidence(
            new CredentialStatus(CredentialStatus::SUSPENDED),
            new DateTimeImmutable('2026-08-10T13:44:00Z'),
            'status-resolver'
        );

        $assessment = new CredentialStatusAssessment(
            false,
            $evidence,
            ['credential_suspended'],
            ['status close to freshness limit']
        );

        self::assertFalse($assessment->acceptable());
        self::assertSame(
            ['credential_suspended'],
            $assessment->violations()
        );
        self::assertSame(
            ['status close to freshness limit'],
            $assessment->warnings()
        );
    }

    public function testStatusContractsAreTyped(): void
    {
        $resolver = new \ReflectionMethod(
            CredentialStatusResolverInterface::class,
            'resolve'
        );
        $policy = new \ReflectionMethod(
            CredentialStatusPolicyInterface::class,
            'assess'
        );

        self::assertSame(
            CredentialStatusEvidence::class,
            (string) $resolver->getReturnType()
        );
        self::assertSame(
            CredentialStatusAssessment::class,
            (string) $policy->getReturnType()
        );

        self::assertTrue(
            (new \ReflectionClass(
                CredentialFreshnessPolicyInterface::class
            ))->isInterface()
        );
    }

    public function testStatusLayerRemainsTransportAndStorageNeutral(): void
    {
        foreach ([
            CredentialStatusResolverInterface::class,
            CredentialStatusPolicyInterface::class,
            CredentialFreshnessPolicyInterface::class,
            CredentialStatus::class,
            CredentialStatusContext::class,
            CredentialStatusEvidence::class,
            CredentialStatusAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('OCSP', $source);
            self::assertStringNotContainsString('CRL', $source);
        }
    }
}
