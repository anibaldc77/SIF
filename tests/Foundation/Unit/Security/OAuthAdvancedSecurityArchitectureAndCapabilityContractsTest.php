<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthAdvancedSecurityPolicyInterface;
use Sif\Foundation\Security\Contracts\OAuthAdvancedSecurityProfileProviderInterface;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationDetailsValidatorInterface;
use Sif\Foundation\Security\Contracts\OAuthClientLifecycleManagerInterface;
use Sif\Foundation\Security\Contracts\OAuthProofOfPossessionVerifierInterface;
use Sif\Foundation\Security\Contracts\OAuthPushedAuthorizationRequestServiceInterface;
use Sif\Foundation\Security\OAuth\Advanced\OAuthAdvancedSecurityCapability;
use Sif\Foundation\Security\OAuth\Advanced\OAuthAdvancedSecurityProfile;
use Sif\Foundation\Security\OAuth\Advanced\OAuthAdvancedSecurityRequirement;

final class OAuthAdvancedSecurityArchitectureAndCapabilityContractsTest extends TestCase
{
    public function testAdvancedSecurityCapabilityIsExplicit(): void
    {
        $capability = new OAuthAdvancedSecurityCapability(
            OAuthAdvancedSecurityCapability::DPOP
        );

        self::assertSame('dpop', $capability->value());
    }

    public function testSecurityProfileCanAdvertiseCapabilities(): void
    {
        $profile = new OAuthAdvancedSecurityProfile(
            'high-security',
            [
                new OAuthAdvancedSecurityCapability(
                    OAuthAdvancedSecurityCapability::PAR
                ),
                new OAuthAdvancedSecurityCapability(
                    OAuthAdvancedSecurityCapability::DPOP
                ),
            ]
        );

        self::assertTrue(
            $profile->supports(
                new OAuthAdvancedSecurityCapability(
                    OAuthAdvancedSecurityCapability::PAR
                )
            )
        );
        self::assertFalse(
            $profile->supports(
                new OAuthAdvancedSecurityCapability(
                    OAuthAdvancedSecurityCapability::RAR
                )
            )
        );
    }

    public function testRequirementKeepsCapabilityAndRequiredFlagExplicit(): void
    {
        $requirement = new OAuthAdvancedSecurityRequirement(
            new OAuthAdvancedSecurityCapability(
                OAuthAdvancedSecurityCapability::PAR
            ),
            true
        );

        self::assertSame(
            OAuthAdvancedSecurityCapability::PAR,
            $requirement->capability()->value()
        );
        self::assertTrue($requirement->required());
    }

    public function testAdvancedContractsRemainInfrastructureNeutral(): void
    {
        foreach ([
            OAuthAdvancedSecurityProfileProviderInterface::class,
            OAuthAdvancedSecurityPolicyInterface::class,
            OAuthPushedAuthorizationRequestServiceInterface::class,
            OAuthProofOfPossessionVerifierInterface::class,
            OAuthAuthorizationDetailsValidatorInterface::class,
            OAuthClientLifecycleManagerInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString(
                'http_response_code',
                strtolower($source)
            );
        }
    }

    public function testAdvancedSecurityLayerDoesNotDependOnConcreteProviders(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/OAuth/Advanced';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('Okta', $source);
            self::assertStringNotContainsString('Microsoft', $source);
            self::assertStringNotContainsString('Auth0', $source);
        }
    }

    public function testI1DefinesBoundariesWithoutPrematureProtocolImplementation(): void
    {
        foreach ([
            OAuthPushedAuthorizationRequestServiceInterface::class,
            OAuthProofOfPossessionVerifierInterface::class,
            OAuthAuthorizationDetailsValidatorInterface::class,
        ] as $class) {
            self::assertTrue(interface_exists($class));
        }
    }
}
