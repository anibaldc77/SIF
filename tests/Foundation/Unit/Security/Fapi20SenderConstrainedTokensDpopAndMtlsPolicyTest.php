<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FapiDPoPPolicyInterface;
use Sif\Foundation\Security\Contracts\FapiMtlsCertificateBindingPolicyInterface;
use Sif\Foundation\Security\Contracts\FapiSenderConstraintPolicyInterface;
use Sif\Foundation\Security\Contracts\FapiSenderConstraintRequirementsProviderInterface;
use Sif\Foundation\Security\Fapi\FapiSenderConstraintAssessment;
use Sif\Foundation\Security\Fapi\FapiSenderConstraintContext;
use Sif\Foundation\Security\Fapi\FapiSenderConstraintMethod;
use Sif\Foundation\Security\Fapi\FapiSenderConstraintRequirements;

final class Fapi20SenderConstrainedTokensDpopAndMtlsPolicyTest extends TestCase
{
    public function testSenderConstraintMethodSupportsDpopAndMtls(): void
    {
        self::assertSame(
            'dpop',
            (new FapiSenderConstraintMethod(
                FapiSenderConstraintMethod::DPOP
            ))->value()
        );

        self::assertSame(
            'mtls',
            (new FapiSenderConstraintMethod(
                FapiSenderConstraintMethod::MTLS
            ))->value()
        );
    }

    public function testRequirementsCanAllowBothSenderConstraintMethods(): void
    {
        $requirements = new FapiSenderConstraintRequirements(
            true,
            [
                new FapiSenderConstraintMethod(
                    FapiSenderConstraintMethod::DPOP
                ),
                new FapiSenderConstraintMethod(
                    FapiSenderConstraintMethod::MTLS
                ),
            ]
        );

        self::assertTrue($requirements->required());
        self::assertTrue(
            $requirements->allows(
                new FapiSenderConstraintMethod(
                    FapiSenderConstraintMethod::DPOP
                )
            )
        );
        self::assertTrue(
            $requirements->allows(
                new FapiSenderConstraintMethod(
                    FapiSenderConstraintMethod::MTLS
                )
            )
        );
    }

    public function testContextKeepsClientResourceAndMethodExplicit(): void
    {
        $context = new FapiSenderConstraintContext(
            'client-001',
            'https://api.example.test',
            new FapiSenderConstraintMethod(
                FapiSenderConstraintMethod::DPOP
            )
        );

        self::assertSame('client-001', $context->clientId());
        self::assertSame(
            'https://api.example.test',
            $context->resource()
        );
        self::assertSame('dpop', $context->method()->value());
    }

    public function testAssessmentRepresentsViolationsExplicitly(): void
    {
        $assessment = new FapiSenderConstraintAssessment(
            false,
            ['sender_constraint_required']
        );

        self::assertFalse($assessment->compliant());
        self::assertSame(
            ['sender_constraint_required'],
            $assessment->violations()
        );
    }

    public function testSenderConstraintPolicyReturnsTypedAssessment(): void
    {
        $method = new \ReflectionMethod(
            FapiSenderConstraintPolicyInterface::class,
            'assess'
        );

        self::assertSame(
            FapiSenderConstraintAssessment::class,
            (string) $method->getReturnType()
        );
    }

    public function testRequirementsProviderIsTyped(): void
    {
        $method = new \ReflectionMethod(
            FapiSenderConstraintRequirementsProviderInterface::class,
            'requirements'
        );

        self::assertSame(
            FapiSenderConstraintRequirements::class,
            (string) $method->getReturnType()
        );
    }

    public function testDpopAndMtlsRemainBehindIndependentPolicies(): void
    {
        self::assertTrue(
            (new \ReflectionClass(
                FapiDPoPPolicyInterface::class
            ))->isInterface()
        );

        self::assertTrue(
            (new \ReflectionClass(
                FapiMtlsCertificateBindingPolicyInterface::class
            ))->isInterface()
        );
    }

    public function testSenderConstraintLayerRemainsCryptoAndInfrastructureNeutral(): void
    {
        foreach ([
            FapiSenderConstraintPolicyInterface::class,
            FapiSenderConstraintRequirementsProviderInterface::class,
            FapiDPoPPolicyInterface::class,
            FapiMtlsCertificateBindingPolicyInterface::class,
            FapiSenderConstraintRequirements::class,
            FapiSenderConstraintContext::class,
            FapiSenderConstraintAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}
