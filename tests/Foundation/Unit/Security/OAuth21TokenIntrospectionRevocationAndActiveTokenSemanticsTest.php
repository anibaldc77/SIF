<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthTokenIntrospectorInterface;
use Sif\Foundation\Security\Contracts\OAuthTokenRevokerInterface;
use Sif\Foundation\Security\Contracts\OAuthTokenStatusProviderInterface;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientId;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthScope;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthTokenIntrospection;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthTokenRevocationRequest;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthTokenStatus;

final class OAuth21TokenIntrospectionRevocationAndActiveTokenSemanticsTest extends TestCase
{
    public function testActiveTokenRequiresNotRevokedAndNotExpired(): void
    {
        $status = new OAuthTokenStatus(
            false,
            new DateTimeImmutable('2026-08-08T12:00:00Z')
        );

        self::assertTrue(
            $status->activeAt(
                new DateTimeImmutable('2026-08-08T11:59:59Z')
            )
        );
        self::assertFalse(
            $status->activeAt(
                new DateTimeImmutable('2026-08-08T12:00:00Z')
            )
        );
    }

    public function testRevokedTokenIsInactiveBeforeExpiration(): void
    {
        $status = new OAuthTokenStatus(
            true,
            new DateTimeImmutable('2026-08-08T12:00:00Z')
        );

        self::assertFalse(
            $status->activeAt(
                new DateTimeImmutable('2026-08-08T11:00:00Z')
            )
        );
    }

    public function testIntrospectionCarriesProtocolRelevantMetadata(): void
    {
        $result = new OAuthTokenIntrospection(
            true,
            new OAuthClientId('client-001'),
            [new OAuthScope('profile')],
            new DateTimeImmutable('2026-08-08T12:00:00Z'),
            'user-001'
        );

        self::assertTrue($result->active());
        self::assertSame('client-001', $result->clientId()->value());
        self::assertSame('user-001', $result->subject());
        self::assertCount(1, $result->scopes());
    }

    public function testRevocationRequestKeepsTokenTypeHintOptional(): void
    {
        $request = new OAuthTokenRevocationRequest(
            'opaque-token',
            'refresh_token'
        );

        self::assertSame('opaque-token', $request->token());
        self::assertSame(
            'refresh_token',
            $request->tokenTypeHint()
        );
    }

    public function testIntrospectionAndRevocationContractsRemainInfrastructureNeutral(): void
    {
        foreach ([
            OAuthTokenIntrospectorInterface::class,
            OAuthTokenRevokerInterface::class,
            OAuthTokenStatusProviderInterface::class,
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

    public function testIntrospectionModelDoesNotRequireJwt(): void
    {
        $reflection = new \ReflectionClass(
            OAuthTokenIntrospection::class
        );

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('JWT', $source);
        self::assertStringNotContainsString('JWS', $source);
        self::assertStringNotContainsString('JWK', $source);
    }
}
