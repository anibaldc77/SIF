<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthClientCredentialsTokenIssuerInterface;
use Sif\Foundation\Security\Contracts\OAuthDeviceAuthorizationApproverInterface;
use Sif\Foundation\Security\Contracts\OAuthDeviceAuthorizationRepositoryInterface;
use Sif\Foundation\Security\Contracts\OAuthMachineIdentityResolverInterface;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientCredentialsGrant;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientId;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthDeviceAuthorization;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthDeviceAuthorizationStatus;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthDeviceCode;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthMachinePrincipal;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthScope;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthUserCode;

final class OAuth21DeviceAuthorizationClientCredentialsAndMachineIdentityContractsTest extends TestCase
{
    public function testDeviceCodeHasLifetimeAndPollingInterval(): void
    {
        $code = new OAuthDeviceCode(
            'device-code-001',
            new OAuthClientId('client-001'),
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            new DateTimeImmutable('2026-08-08T10:15:00Z'),
            5
        );

        self::assertSame(5, $code->pollIntervalSeconds());
        self::assertFalse(
            $code->expiredAt(
                new DateTimeImmutable('2026-08-08T10:14:59Z')
            )
        );
    }

    public function testDeviceAuthorizationStartsPending(): void
    {
        $authorization = new OAuthDeviceAuthorization(
            new OAuthDeviceCode(
                'device-code-001',
                new OAuthClientId('client-001'),
                new DateTimeImmutable('2026-08-08T10:00:00Z'),
                new DateTimeImmutable('2026-08-08T10:15:00Z'),
                5
            ),
            new OAuthUserCode('ABCD-EFGH'),
            [new OAuthScope('profile')],
            new OAuthDeviceAuthorizationStatus(
                OAuthDeviceAuthorizationStatus::PENDING
            )
        );

        self::assertFalse($authorization->approved());
        self::assertNull($authorization->subject());
    }

    public function testClientCredentialsGrantCarriesClientAndScopes(): void
    {
        $grant = new OAuthClientCredentialsGrant(
            new OAuthClientId('machine-client'),
            [new OAuthScope('jobs.execute')]
        );

        self::assertSame(
            'machine-client',
            $grant->clientId()->value()
        );
        self::assertCount(1, $grant->scopes());
    }

    public function testMachinePrincipalIsDistinctFromHumanIdentity(): void
    {
        $principal = new OAuthMachinePrincipal(
            'service:billing-worker',
            new OAuthClientId('machine-client')
        );

        self::assertSame(
            'service:billing-worker',
            $principal->subject()
        );
        self::assertSame(
            'machine-client',
            $principal->clientId()->value()
        );
    }

    public function testContractsRemainInfrastructureNeutral(): void
    {
        foreach ([
            OAuthDeviceAuthorizationRepositoryInterface::class,
            OAuthDeviceAuthorizationApproverInterface::class,
            OAuthMachineIdentityResolverInterface::class,
            OAuthClientCredentialsTokenIssuerInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('http_response_code', strtolower($source));
        }
    }

    public function testDeviceFlowDomainDoesNotOwnPollingTransportOrUi(): void
    {
        foreach ([
            OAuthDeviceCode::class,
            OAuthDeviceAuthorization::class,
            OAuthUserCode::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('sleep(', strtolower($source));
            self::assertStringNotContainsString('header(', strtolower($source));
            self::assertStringNotContainsString('<html', strtolower($source));
        }
    }
}
