<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authorization\Permission\PermissionIdentifier;
use Sif\Foundation\Security\Authorization\Permission\PermissionSet;
use Sif\Foundation\Security\Contracts\AccessTokenValidatorInterface;
use Sif\Foundation\Security\OAuth2\AccessToken;
use Sif\Foundation\Security\OAuth2\Authorization\OAuthAccessTokenAuthorizationAttributes;
use Sif\Foundation\Security\OAuth2\Authorization\ScopePermissionMap;
use Sif\Foundation\Security\OAuth2\BearerAuthenticationFailureFactory;
use Sif\Foundation\Security\OAuth2\BearerErrorCode;
use Sif\Foundation\Security\OAuth2\BearerTokenExtractor;
use Sif\Foundation\Security\OAuth2\Http\BearerPrincipalFactory;
use Sif\Foundation\Security\OAuth2\Http\OAuth2ResourceServerAuthenticator;
use Sif\Foundation\Security\OAuth2\Http\ResourceServerApiBridge;
use Sif\Foundation\Security\OAuth2\Http\ResourceServerAuthorizationContextFactory;
use Sif\Foundation\Security\OAuth2\ScopeSet;
use Sif\Foundation\Security\OAuth2\ValidatedAccessToken;

final class OAuth2HttpResourceServerAuthenticationAndApiIntegrationTest extends TestCase
{
    public function testValidBearerTokenCreatesExistingAuthenticatedPrincipal(): void
    {
        $now = new DateTimeImmutable('2026-08-07T20:00:00+00:00');

        $result = $this->authenticator(true)->authenticate(
            'Bearer opaque-token-material-abcdefghijklmnopqrstuvwxyz',
            $now
        );

        self::assertTrue($result->isAuthenticated());

        $principal = $result->principal();
        self::assertNotNull($principal);

        self::assertSame(
            'api-user',
            $principal->identity()->id()->value()
        );
        self::assertSame(
            50,
            $principal->evidence()->level()->value()
        );
    }

    public function testMissingBearerTokenReturnsCanonical401Failure(): void
    {
        $result = $this->authenticator(true)->authenticate(
            '',
            new DateTimeImmutable('2026-08-07T20:00:00+00:00')
        );

        self::assertFalse($result->isAuthenticated());

        $failure = $result->failure();
        self::assertNotNull($failure);

        self::assertSame(
            401,
            $failure->statusCode()
        );
        self::assertSame(
            BearerErrorCode::InvalidToken,
            $failure->error()->code()
        );
    }

    public function testMalformedBearerHeaderReturnsInvalidRequest(): void
    {
        $result = $this->authenticator(true)->authenticate(
            'Basic abcdefghijklmnopqrstuvwxyz',
            new DateTimeImmutable('2026-08-07T20:00:00+00:00')
        );

        self::assertFalse($result->isAuthenticated());

        $failure = $result->failure();
        self::assertNotNull($failure);

        self::assertSame(
            BearerErrorCode::InvalidRequest,
            $failure->error()->code()
        );
    }

    public function testInvalidOrExpiredTokenReturnsCanonical401Failure(): void
    {
        $result = $this->authenticator(false)->authenticate(
            'Bearer opaque-token-material-abcdefghijklmnopqrstuvwxyz',
            new DateTimeImmutable('2026-08-07T20:00:00+00:00')
        );

        self::assertFalse($result->isAuthenticated());

        $failure = $result->failure();
        self::assertNotNull($failure);

        self::assertSame(
            401,
            $failure->statusCode()
        );
    }

    public function testApiBridgeExposesAuthorizationContextOnlyAfterAuthentication(): void
    {
        $bridge = $this->bridge(true);

        $result = $bridge->authenticate(
            'Bearer opaque-token-material-abcdefghijklmnopqrstuvwxyz',
            new DateTimeImmutable('2026-08-07T20:00:00+00:00')
        );

        self::assertTrue(
            $result->authentication()->isAuthenticated()
        );

        $context = $result->authorizationContext();
        self::assertNotNull($context);

        self::assertSame(
            ['invoice.read'],
            $context->permissions()->values()
        );
    }

    public function testHttpIntegrationDoesNotCreateResponseOrMutateSession(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/OAuth2/Http';

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString('new Response', $source);
            self::assertStringNotContainsString('redirect(', strtolower($source));
            self::assertStringNotContainsString('Session', $source);
            self::assertStringNotContainsString('setcookie(', strtolower($source));
        }
    }

    private function bridge(bool $valid): ResourceServerApiBridge
    {
        return new ResourceServerApiBridge(
            $this->authenticator($valid),
            new ResourceServerAuthorizationContextFactory(
                new ScopePermissionMap([
                    'invoice.read' => new PermissionSet([
                        new PermissionIdentifier('invoice.read'),
                    ]),
                ]),
                new OAuthAccessTokenAuthorizationAttributes()
            )
        );
    }

    private function authenticator(
        bool $valid
    ): OAuth2ResourceServerAuthenticator {
        $validator = new class($valid) implements AccessTokenValidatorInterface {
            public function __construct(private bool $valid)
            {
            }

            public function validate(
                AccessToken $token,
                DateTimeImmutable $now
            ): ?ValidatedAccessToken {
                if (!$this->valid) {
                    return null;
                }

                return new ValidatedAccessToken(
                    new \Sif\Foundation\Security\Identity\IdentityId('api-user'),
                    new ScopeSet(['invoice.read']),
                    $now->modify('+1 hour'),
                    $now,
                    ['client_id' => 'api-client']
                );
            }
        };

        return new OAuth2ResourceServerAuthenticator(
            new BearerTokenExtractor(),
            $validator,
            new BearerPrincipalFactory(),
            new BearerAuthenticationFailureFactory(),
            'sif-api'
        );
    }
}
