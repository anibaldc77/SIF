<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\AuthorizationServerInterface;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationRequestValidatorInterface;
use Sif\Foundation\Security\Contracts\OAuthClientRepositoryInterface;
use Sif\Foundation\Security\Contracts\OAuthScopeRepositoryInterface;
use Sif\Foundation\Security\Contracts\OAuthTokenRequestValidatorInterface;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationRequest;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientId;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRedirectUri;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthScope;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthTokenRequest;

final class OAuth21AuthorizationServerArchitectureTest extends TestCase
{
    public function testClientModelIsExplicitAndTransportNeutral(): void
    {
        $client = new OAuthClient(
            new OAuthClientId('client-001'),
            'Example client',
            false,
            [new OAuthRedirectUri('https://example.test/callback')],
            [new OAuthScope('profile')]
        );

        self::assertSame('client-001', $client->id()->value());
        self::assertFalse($client->confidential());
        self::assertSame(
            'https://example.test/callback',
            $client->redirectUris()[0]->value()
        );
    }

    public function testAuthorizationRequestCarriesCoreProtocolFields(): void
    {
        $request = new OAuthAuthorizationRequest(
            new OAuthClientId('client-001'),
            new OAuthRedirectUri('https://example.test/callback'),
            'code',
            [new OAuthScope('profile')],
            'state-001'
        );

        self::assertSame('code', $request->responseType());
        self::assertSame('state-001', $request->state());
        self::assertCount(1, $request->scopes());
    }

    public function testTokenRequestIsGrantAgnosticAtArchitectureLayer(): void
    {
        $request = new OAuthTokenRequest(
            'authorization_code',
            new OAuthClientId('client-001'),
            ['code' => 'authorization-code-001']
        );

        self::assertSame(
            'authorization_code',
            $request->grantType()
        );
        self::assertSame(
            'authorization-code-001',
            $request->parameters()['code']
        );
    }

    public function testContractsRemainInfrastructureNeutral(): void
    {
        foreach ([
            AuthorizationServerInterface::class,
            OAuthClientRepositoryInterface::class,
            OAuthScopeRepositoryInterface::class,
            OAuthAuthorizationRequestValidatorInterface::class,
            OAuthTokenRequestValidatorInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
        }
    }

    public function testAuthorizationServerDoesNotDependOnConcreteProvider(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/OAuth/AuthorizationServer';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('Okta', $source);
            self::assertStringNotContainsString('Microsoft', $source);
        }
    }

    public function testArchitectureDoesNotOwnHttpResponseTranslation(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/OAuth/AuthorizationServer';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString(
                'http_response_code',
                strtolower($source)
            );
            self::assertStringNotContainsString(
                'header(',
                strtolower($source)
            );
        }
    }
}
