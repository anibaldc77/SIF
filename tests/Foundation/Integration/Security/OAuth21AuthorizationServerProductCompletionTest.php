<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Integration\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAccessToken;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationCode;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientCredentialsGrant;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientId;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthDeviceAuthorization;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthDeviceAuthorizationStatus;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthDeviceCode;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthJwtClaims;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthMachinePrincipal;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthPkceChallenge;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthPkceMethod;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRedirectUri;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthScope;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthTokenStatus;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthUserCode;

final class OAuth21AuthorizationServerProductCompletionTest extends TestCase
{
    public function testAuthorizationCodeClientAndPkceConceptsCompose(): void
    {
        $client = new OAuthClient(
            new OAuthClientId('client-001'),
            'Example',
            false,
            [new OAuthRedirectUri('https://example.test/callback')],
            [new OAuthScope('profile')]
        );

        $code = new OAuthAuthorizationCode(
            'code-001',
            $client->id(),
            $client->redirectUris()[0],
            $client->allowedScopes(),
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            new DateTimeImmutable('2026-08-08T10:05:00Z'),
            'challenge',
            OAuthPkceMethod::S256
        );

        $challenge = new OAuthPkceChallenge(
            'challenge',
            new OAuthPkceMethod(OAuthPkceMethod::S256)
        );

        self::assertSame('client-001', $code->clientId()->value());
        self::assertSame('S256', $challenge->method()->value());
    }

    public function testAccessTokenStatusAndJwtClaimsRemainComposable(): void
    {
        $access = new OAuthAccessToken(
            'access-001',
            new OAuthClientId('client-001'),
            [new OAuthScope('api.read')],
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            new DateTimeImmutable('2026-08-08T11:00:00Z')
        );

        $claims = new OAuthJwtClaims(
            'https://issuer.example.test',
            'user-001',
            ['api://example'],
            $access->clientId(),
            $access->scopes(),
            $access->issuedAt(),
            $access->expiresAt(),
            'jti-001'
        );

        $status = new OAuthTokenStatus(
            false,
            $access->expiresAt()
        );

        self::assertSame('user-001', $claims->subject());
        self::assertTrue(
            $status->activeAt(
                new DateTimeImmutable('2026-08-08T10:30:00Z')
            )
        );
    }

    public function testDeviceAndMachineIdentityFlowsRemainDistinct(): void
    {
        $device = new OAuthDeviceAuthorization(
            new OAuthDeviceCode(
                'device-001',
                new OAuthClientId('device-client'),
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

        $grant = new OAuthClientCredentialsGrant(
            new OAuthClientId('machine-client'),
            [new OAuthScope('jobs.execute')]
        );

        $machine = new OAuthMachinePrincipal(
            'service:worker',
            $grant->clientId()
        );

        self::assertFalse($device->approved());
        self::assertSame('service:worker', $machine->subject());
    }

    public function testAuthorizationServerFoundationRemainsInfrastructureNeutral(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/OAuth/AuthorizationServer';

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $directory,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($files as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('http_response_code', strtolower($source));
        }
    }

    public function testAuthorizationServerFoundationRemainsProviderNeutral(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/OAuth/AuthorizationServer';

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $directory,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($files as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            self::assertIsString($source);
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('Okta', $source);
            self::assertStringNotContainsString('Microsoft', $source);
            self::assertStringNotContainsString('Auth0', $source);
        }
    }

    public function testProductCompletionRetainsContractBoundaries(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\AuthorizationServerInterface::class,
            \Sif\Foundation\Security\Contracts\OAuthTokenIssuerInterface::class,
            \Sif\Foundation\Security\Contracts\OAuthClientAuthenticatorInterface::class,
            \Sif\Foundation\Security\Contracts\OAuthTokenIntrospectorInterface::class,
            \Sif\Foundation\Security\Contracts\OAuthJwtAccessTokenSignerInterface::class,
            \Sif\Foundation\Security\Contracts\OAuthDeviceAuthorizationRepositoryInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract));
        }
    }
}
