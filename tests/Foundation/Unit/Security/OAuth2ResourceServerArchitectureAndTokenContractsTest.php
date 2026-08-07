<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\AccessTokenValidatorInterface;
use Sif\Foundation\Security\Contracts\BearerTokenExtractorInterface;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\OAuth2\AccessToken;
use Sif\Foundation\Security\OAuth2\ScopeSet;
use Sif\Foundation\Security\OAuth2\ValidatedAccessToken;

final class OAuth2ResourceServerArchitectureAndTokenContractsTest extends TestCase
{
    public function testAccessTokenIsOpaqueRedactedAndNonSerializable(): void
    {
        $token = new AccessToken(
            'opaque-token-material-abcdefghijklmnopqrstuvwxyz'
        );

        self::assertSame('[REDACTED]', (string) $token);
        self::assertSame(
            hash('sha256', 'opaque-token-material-abcdefghijklmnopqrstuvwxyz'),
            $token->fingerprint()
        );

        $this->expectException(\LogicException::class);
        serialize($token);
    }

    public function testAccessTokenCanOnlyBeRevealedThroughExplicitCallback(): void
    {
        $token = new AccessToken(
            'opaque-token-material-abcdefghijklmnopqrstuvwxyz'
        );

        $value = $token->expose(
            static fn (string $raw): string => $raw
        );

        self::assertSame(
            'opaque-token-material-abcdefghijklmnopqrstuvwxyz',
            $value
        );
    }

    public function testScopesAreDeterministicAndDeduplicated(): void
    {
        $scopes = new ScopeSet([
            'invoice.read',
            'profile',
            'invoice.read',
        ]);

        self::assertSame(2, $scopes->count());
        self::assertSame(
            ['invoice.read', 'profile'],
            $scopes->values()
        );
        self::assertTrue($scopes->contains('profile'));
    }

    public function testValidatedTokenCapturesSubjectScopesExpiryAndAttributes(): void
    {
        $issuedAt = new DateTimeImmutable('2026-08-07T12:00:00+00:00');
        $expiresAt = new DateTimeImmutable('2026-08-07T13:00:00+00:00');

        $validated = new ValidatedAccessToken(
            new IdentityId('oauth-subject'),
            new ScopeSet(['api.read']),
            $expiresAt,
            $issuedAt,
            [
                'client_id' => 'resource-client',
                'tenant.id' => 7,
            ]
        );

        self::assertSame(
            'oauth-subject',
            $validated->subject()->value()
        );
        self::assertSame(['api.read'], $validated->scopes()->values());
        self::assertTrue(
            $validated->isActiveAt(
                new DateTimeImmutable('2026-08-07T12:30:00+00:00')
            )
        );
        self::assertFalse($validated->isActiveAt($expiresAt));
        self::assertSame(7, $validated->attributes()['tenant.id']);
    }

    public function testContractsRemainTokenFormatAndTransportNeutral(): void
    {
        foreach ([
            AccessTokenValidatorInterface::class,
            BearerTokenExtractorInterface::class,
        ] as $contract) {
            $reflection = new \ReflectionClass($contract);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('JWT', $source);
            self::assertStringNotContainsString('JWK', $source);
            self::assertStringNotContainsString('OpenID', $source);
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('curl', strtolower($source));
        }
    }

    public function testResourceServerVocabularyDoesNotIssueTokens(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/OAuth2';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString('AuthorizationCode', $source);
            self::assertStringNotContainsString('RefreshToken', $source);
            self::assertStringNotContainsString('issueToken', $source);
            self::assertStringNotContainsString('tokenEndpoint', $source);
        }
    }
}
