<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthAccessTokenRepositoryInterface;
use Sif\Foundation\Security\Contracts\OAuthRefreshTokenRepositoryInterface;
use Sif\Foundation\Security\Contracts\OAuthRefreshTokenRotatorInterface;
use Sif\Foundation\Security\Contracts\OAuthTokenIssuerInterface;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAccessToken;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientId;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRefreshToken;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRefreshTokenFamilyId;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthScope;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthTokenPair;

final class OAuth21AccessRefreshTokenLifetimeRotationAndIssuanceContractsTest extends TestCase
{
    public function testAccessTokenHasExplicitLifetimeAndScopes(): void
    {
        $token = new OAuthAccessToken(
            'access-001',
            new OAuthClientId('client-001'),
            [new OAuthScope('profile')],
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            new DateTimeImmutable('2026-08-08T11:00:00Z')
        );

        self::assertSame('access-001', $token->value());
        self::assertCount(1, $token->scopes());
        self::assertFalse(
            $token->expiredAt(
                new DateTimeImmutable('2026-08-08T10:59:59Z')
            )
        );
        self::assertTrue(
            $token->expiredAt(
                new DateTimeImmutable('2026-08-08T11:00:00Z')
            )
        );
    }

    public function testRefreshTokenCarriesRotationFamily(): void
    {
        $family = new OAuthRefreshTokenFamilyId('family-001');

        $token = new OAuthRefreshToken(
            'refresh-002',
            new OAuthClientId('client-001'),
            $family,
            [new OAuthScope('profile')],
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            new DateTimeImmutable('2026-09-08T10:00:00Z'),
            'refresh-001'
        );

        self::assertSame('family-001', $token->familyId()->value());
        self::assertSame('refresh-001', $token->replacesToken());
    }

    public function testTokenPairSupportsOptionalRefreshToken(): void
    {
        $access = new OAuthAccessToken(
            'access-001',
            new OAuthClientId('client-001'),
            [],
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            new DateTimeImmutable('2026-08-08T11:00:00Z')
        );

        $pair = new OAuthTokenPair($access);

        self::assertSame('access-001', $pair->accessToken()->value());
        self::assertNull($pair->refreshToken());
    }

    public function testTokenContractsRemainStorageAndFormatNeutral(): void
    {
        foreach ([
            OAuthTokenIssuerInterface::class,
            OAuthAccessTokenRepositoryInterface::class,
            OAuthRefreshTokenRepositoryInterface::class,
            OAuthRefreshTokenRotatorInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('JWT', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
        }
    }

    public function testRefreshRepositoryCanRevokeEntireRotationFamily(): void
    {
        $reflection = new \ReflectionMethod(
            OAuthRefreshTokenRepositoryInterface::class,
            'revokeFamily'
        );

        self::assertSame(
            'void',
            (string) $reflection->getReturnType()
        );
        self::assertCount(1, $reflection->getParameters());
    }

    public function testTokenDomainDoesNotOwnHttpTranslation(): void
    {
        foreach ([
            OAuthAccessToken::class,
            OAuthRefreshToken::class,
            OAuthTokenPair::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

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
