<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\TokenIntrospectorInterface;
use Sif\Foundation\Security\OAuth2\AccessToken;
use Sif\Foundation\Security\OAuth2\Introspection\ArrayTokenIntrospectionMapper;
use Sif\Foundation\Security\OAuth2\Introspection\OpaqueAccessTokenValidator;
use Sif\Foundation\Security\OAuth2\Introspection\TokenIntrospectionResult;
use Sif\Foundation\Security\OAuth2\ScopeSet;

final class OAuth2TokenIntrospectionAndOpaqueTokenValidationTest extends TestCase
{
    public function testMapperBuildsActiveIntrospectionResult(): void
    {
        $result = (new ArrayTokenIntrospectionMapper())->map([
            'active' => true,
            'sub' => 'opaque-user',
            'scope' => 'api.read invoice.read',
            'exp' => 1786136400,
            'iat' => 1786132800,
            'client_id' => 'client-opaque',
            'tenant.id' => 7,
            'nested' => ['ignored' => true],
        ]);

        self::assertTrue($result->isActive());
        self::assertSame('opaque-user', $result->subject());
        self::assertSame(
            ['api.read', 'invoice.read'],
            $result->scopes()->values()
        );
        self::assertSame(
            'client-opaque',
            $result->attributes()['client_id']
        );
        self::assertArrayNotHasKey(
            'nested',
            $result->attributes()
        );
    }

    public function testInactiveIntrospectionResultProducesInvalidToken(): void
    {
        $introspector = new class implements TokenIntrospectorInterface {
            public function introspect(
                AccessToken $token
            ): TokenIntrospectionResult {
                return new TokenIntrospectionResult(false);
            }
        };

        $validated = (new OpaqueAccessTokenValidator($introspector))->validate(
            new AccessToken(
                'opaque-token-material-abcdefghijklmnopqrstuvwxyz'
            ),
            new DateTimeImmutable('2026-08-07T18:00:00+00:00')
        );

        self::assertNull($validated);
    }

    public function testActiveOpaqueTokenMapsToValidatedAccessToken(): void
    {
        $now = new DateTimeImmutable('2026-08-07T18:00:00+00:00');

        $introspector = new class($now) implements TokenIntrospectorInterface {
            public function __construct(private DateTimeImmutable $now)
            {
            }

            public function introspect(
                AccessToken $token
            ): TokenIntrospectionResult {
                return new TokenIntrospectionResult(
                    true,
                    'opaque-user',
                    new ScopeSet(['api.read']),
                    $this->now->modify('+30 minutes'),
                    $this->now->modify('-5 minutes'),
                    ['client_id' => 'client-opaque']
                );
            }
        };

        $validated = (new OpaqueAccessTokenValidator($introspector))->validate(
            new AccessToken(
                'opaque-token-material-abcdefghijklmnopqrstuvwxyz'
            ),
            $now
        );

        self::assertNotNull($validated);
        self::assertSame(
            'opaque-user',
            $validated->subject()->value()
        );
        self::assertSame(
            ['api.read'],
            $validated->scopes()->values()
        );
    }

    public function testExpiredOpaqueTokenFailsClosed(): void
    {
        $now = new DateTimeImmutable('2026-08-07T18:00:00+00:00');

        $introspector = new class($now) implements TokenIntrospectorInterface {
            public function __construct(private DateTimeImmutable $now)
            {
            }

            public function introspect(
                AccessToken $token
            ): TokenIntrospectionResult {
                return new TokenIntrospectionResult(
                    true,
                    'opaque-user',
                    new ScopeSet(['api.read']),
                    $this->now->modify('-1 second')
                );
            }
        };

        self::assertNull(
            (new OpaqueAccessTokenValidator($introspector))->validate(
                new AccessToken(
                    'opaque-token-material-abcdefghijklmnopqrstuvwxyz'
                ),
                $now
            )
        );
    }

    public function testOpaqueValidationRemainsTransportNeutral(): void
    {
        $reflection = new \ReflectionClass(
            TokenIntrospectorInterface::class
        );

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('curl', strtolower($source));
        self::assertStringNotContainsString('HttpClient', $source);
        self::assertStringNotContainsString('Keycloak', $source);
        self::assertStringNotContainsString('client_secret', $source);
    }

    public function testOpaqueTokenValidatorDoesNotExposeTokenMaterial(): void
    {
        $reflection = new \ReflectionClass(
            OpaqueAccessTokenValidator::class
        );

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('echo ', $source);
        self::assertStringNotContainsString('var_dump', $source);
        self::assertStringNotContainsString('print_r', $source);
    }
}
