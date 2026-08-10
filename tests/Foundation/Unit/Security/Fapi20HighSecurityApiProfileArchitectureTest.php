<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FapiAuthorizationServerPolicyInterface;
use Sif\Foundation\Security\Contracts\FapiClientPolicyInterface;
use Sif\Foundation\Security\Contracts\FapiConformanceEvaluatorInterface;
use Sif\Foundation\Security\Contracts\FapiResourceServerPolicyInterface;
use Sif\Foundation\Security\Contracts\FapiSecurityProfileProviderInterface;
use Sif\Foundation\Security\Fapi\FapiConformanceReport;
use Sif\Foundation\Security\Fapi\FapiSecurityCapability;
use Sif\Foundation\Security\Fapi\FapiSecurityProfile;

final class Fapi20HighSecurityApiProfileArchitectureTest extends TestCase
{
    public function testCapabilitiesRepresentFapiBuildingBlocks(): void
    {
        $capability = new FapiSecurityCapability(
            FapiSecurityCapability::PAR
        );

        self::assertSame('par', $capability->value());
    }

    public function testProfileExpressesMandatoryCapabilities(): void
    {
        $profile = new FapiSecurityProfile(
            'fapi-2-security-profile',
            [
                new FapiSecurityCapability(
                    FapiSecurityCapability::CONFIDENTIAL_CLIENTS
                ),
                new FapiSecurityCapability(
                    FapiSecurityCapability::PAR
                ),
                new FapiSecurityCapability(
                    FapiSecurityCapability::SENDER_CONSTRAINED_TOKENS
                ),
            ]
        );

        self::assertTrue(
            $profile->requires(
                new FapiSecurityCapability(
                    FapiSecurityCapability::PAR
                )
            )
        );
        self::assertFalse(
            $profile->requires(
                new FapiSecurityCapability(
                    FapiSecurityCapability::JARM
                )
            )
        );
    }

    public function testConformanceReportIsExplicit(): void
    {
        $report = new FapiConformanceReport(
            false,
            ['sender_constrained_tokens'],
            ['message signing not enabled']
        );

        self::assertFalse($report->conformant());
        self::assertSame(
            ['sender_constrained_tokens'],
            $report->missingCapabilities()
        );
        self::assertSame(
            ['message signing not enabled'],
            $report->warnings()
        );
    }

    public function testConformanceEvaluatorReturnsTypedReport(): void
    {
        $method = new \ReflectionMethod(
            FapiConformanceEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            FapiConformanceReport::class,
            (string) $method->getReturnType()
        );
    }

    public function testFapiPoliciesAreSeparatedByRole(): void
    {
        foreach ([
            FapiClientPolicyInterface::class,
            FapiAuthorizationServerPolicyInterface::class,
            FapiResourceServerPolicyInterface::class,
            FapiSecurityProfileProviderInterface::class,
        ] as $class) {
            self::assertTrue(
                (new \ReflectionClass($class))->isInterface()
            );
        }
    }

    public function testFapiLayerRemainsProtocolAndInfrastructureNeutral(): void
    {
        foreach ([
            FapiConformanceEvaluatorInterface::class,
            FapiClientPolicyInterface::class,
            FapiAuthorizationServerPolicyInterface::class,
            FapiResourceServerPolicyInterface::class,
            FapiSecurityProfile::class,
            FapiConformanceReport::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}
