<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthJwtAccessTokenSignerInterface;
use Sif\Foundation\Security\Contracts\OAuthJwtClaimsFactoryInterface;
use Sif\Foundation\Security\Contracts\OAuthSigningKeyProviderInterface;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientId;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthJwtClaims;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthScope;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthSignedAccessToken;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthSigningKey;

final class OAuth21JwtAccessTokensSigningClaimsAudienceAndKeyRotationContractsTest extends TestCase
{
    public function testJwtClaimsCarryIssuerSubjectAudienceClientScopesAndLifetime(): void
    {
        $claims = new OAuthJwtClaims(
            'https://issuer.example.test',
            'user-001',
            ['api://payments'],
            new OAuthClientId('client-001'),
            [new OAuthScope('payments.read')],
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            new DateTimeImmutable('2026-08-08T11:00:00Z'),
            'jti-001'
        );

        self::assertSame(
            'https://issuer.example.test',
            $claims->issuer()
        );
        self::assertSame('user-001', $claims->subject());
        self::assertSame(
            ['api://payments'],
            $claims->audience()
        );
        self::assertSame('jti-001', $claims->tokenId());
        self::assertCount(1, $claims->scopes());
    }

    public function testSigningKeyCarriesKidAlgorithmAndOpaqueMaterialReference(): void
    {
        $key = new OAuthSigningKey(
            'kid-2026-08',
            'RS256',
            'vault://oauth/signing/current'
        );

        self::assertSame('kid-2026-08', $key->keyId());
        self::assertSame('RS256', $key->algorithm());
        self::assertSame(
            'vault://oauth/signing/current',
            $key->materialReference()
        );
    }

    public function testSignedAccessTokenKeepsClaimsAndSigningMetadata(): void
    {
        $claims = new OAuthJwtClaims(
            'issuer',
            'subject',
            ['audience'],
            new OAuthClientId('client-001'),
            [],
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            new DateTimeImmutable('2026-08-08T11:00:00Z'),
            'jti-001'
        );

        $token = new OAuthSignedAccessToken(
            'header.payload.signature',
            'kid-001',
            'RS256',
            $claims
        );

        self::assertSame('kid-001', $token->keyId());
        self::assertSame('RS256', $token->algorithm());
        self::assertSame('jti-001', $token->claims()->tokenId());
    }

    public function testSigningContractsRemainCryptoLibraryNeutral(): void
    {
        foreach ([
            OAuthJwtAccessTokenSignerInterface::class,
            OAuthSigningKeyProviderInterface::class,
            OAuthJwtClaimsFactoryInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('firebase', strtolower($source));
            self::assertStringNotContainsString('lcobucci', strtolower($source));
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('PDO', $source);
        }
    }

    public function testSigningKeyProviderSupportsRotationByCurrentAndKidLookup(): void
    {
        $current = new \ReflectionMethod(
            OAuthSigningKeyProviderInterface::class,
            'current'
        );
        $find = new \ReflectionMethod(
            OAuthSigningKeyProviderInterface::class,
            'find'
        );

        self::assertSame(
            OAuthSigningKey::class,
            (string) $current->getReturnType()
        );
        self::assertCount(1, $find->getParameters());
    }

    public function testJwtDomainDoesNotOwnJwksOrHttpPublication(): void
    {
        foreach ([
            OAuthJwtClaims::class,
            OAuthSigningKey::class,
            OAuthSignedAccessToken::class,
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
                'jwks_uri',
                strtolower($source)
            );
        }
    }
}
