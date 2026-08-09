<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthAdvancedSecurityReadinessEvaluatorInterface;
use Sif\Foundation\Security\OAuth\Advanced\OAuthAdvancedSecurityCapabilities;
use Sif\Foundation\Security\OAuth\Advanced\OAuthAdvancedSecurityProductProfile;
use Sif\Foundation\Security\OAuth\Advanced\OAuthAdvancedSecurityReadinessReport;

final class OAuthAdvancedSecurityProductCompletionTest extends TestCase
{
    public function testCapabilitiesRepresentCompletedWp240Surface(): void
    {
        $capabilities = new OAuthAdvancedSecurityCapabilities();

        self::assertTrue($capabilities->pushedAuthorizationRequests());
        self::assertTrue($capabilities->richAuthorizationRequests());
        self::assertTrue($capabilities->jwtSecuredAuthorizationRequests());
        self::assertTrue($capabilities->dpop());
        self::assertTrue($capabilities->senderConstrainedAccessTokens());
        self::assertTrue($capabilities->resourceServerEnforcement());
        self::assertCount(6, $capabilities->toArray());
    }

    public function testProductProfileMakesSecurityRequirementsExplicit(): void
    {
        $profile = new OAuthAdvancedSecurityProductProfile(
            'oauth-advanced-default',
            new OAuthAdvancedSecurityCapabilities()
        );

        self::assertSame('oauth-advanced-default', $profile->name());
        self::assertTrue($profile->requirePkce());
        self::assertTrue($profile->requirePar());
        self::assertTrue($profile->requireSenderConstraint());
        self::assertTrue($profile->capabilities()->dpop());
    }

    public function testReadinessReportCanRepresentReadyProduct(): void
    {
        $report = new OAuthAdvancedSecurityReadinessReport(true);

        self::assertTrue($report->ready());
        self::assertSame([], $report->missingCapabilities());
    }

    public function testReadinessReportCanRepresentMissingCapabilities(): void
    {
        $report = new OAuthAdvancedSecurityReadinessReport(false, ['dpop', 'resource_server_enforcement']);

        self::assertFalse($report->ready());
        self::assertSame(['dpop', 'resource_server_enforcement'], $report->missingCapabilities());
    }

    public function testReadinessEvaluatorContractIsTyped(): void
    {
        $method = new \ReflectionMethod(OAuthAdvancedSecurityReadinessEvaluatorInterface::class, 'evaluate');

        self::assertSame(
            OAuthAdvancedSecurityReadinessReport::class,
            (string) $method->getReturnType()
        );

        $parameters = $method->getParameters();
        self::assertCount(1, $parameters);
        self::assertSame(
            OAuthAdvancedSecurityProductProfile::class,
            (string) $parameters[0]->getType()
        );
    }

    public function testProductCompletionBoundaryRemainsInfrastructureNeutral(): void
    {
        foreach ([
            OAuthAdvancedSecurityReadinessEvaluatorInterface::class,
            OAuthAdvancedSecurityCapabilities::class,
            OAuthAdvancedSecurityProductProfile::class,
            OAuthAdvancedSecurityReadinessReport::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}
