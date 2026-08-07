<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Integration\Security;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authorization\Permission\PermissionIdentifier;
use Sif\Foundation\Security\Authorization\Permission\PermissionSet;
use Sif\Foundation\Security\Contracts\AccessTokenValidatorInterface;
use Sif\Foundation\Security\Contracts\JwkSignatureVerifierInterface;
use Sif\Foundation\Security\Contracts\JwtParserInterface;
use Sif\Foundation\Security\Contracts\TokenIntrospectorInterface;
use Sif\Foundation\Security\OAuth2\AccessToken;
use Sif\Foundation\Security\OAuth2\Authorization\OAuthAccessTokenAuthorizationAttributes;
use Sif\Foundation\Security\OAuth2\Authorization\ScopePermissionMap;
use Sif\Foundation\Security\OAuth2\BearerAuthenticationFailureFactory;
use Sif\Foundation\Security\OAuth2\BearerTokenExtractor;
use Sif\Foundation\Security\OAuth2\Http\BearerPrincipalFactory;
use Sif\Foundation\Security\OAuth2\Http\OAuth2ResourceServerAuthenticator;
use Sif\Foundation\Security\OAuth2\Http\ResourceServerApiBridge;
use Sif\Foundation\Security\OAuth2\Http\ResourceServerAuthorizationContextFactory;
use Sif\Foundation\Security\OAuth2\Introspection\OpaqueAccessTokenValidator;
use Sif\Foundation\Security\OAuth2\Introspection\TokenIntrospectionResult;
use Sif\Foundation\Security\OAuth2\Jwks\InMemoryJwkSetProvider;
use Sif\Foundation\Security\OAuth2\Jwks\Jwk;
use Sif\Foundation\Security\OAuth2\Jwks\JwkResolver;
use Sif\Foundation\Security\OAuth2\Jwks\JwkSet;
use Sif\Foundation\Security\OAuth2\Jwks\JwksJwtSignatureVerifier;
use Sif\Foundation\Security\OAuth2\Jwt\JwtAccessTokenValidator;
use Sif\Foundation\Security\OAuth2\Jwt\JwtClaims;
use Sif\Foundation\Security\OAuth2\Jwt\JwtHeader;
use Sif\Foundation\Security\OAuth2\Jwt\JwtValidationPolicy;
use Sif\Foundation\Security\OAuth2\Jwt\ParsedJwt;
use Sif\Foundation\Security\OAuth2\ScopeSet;
use Sif\Foundation\Security\OAuth2\ValidatedAccessToken;

final class OAuth2ResourceServerProductCompletionTest extends TestCase
{
    public function testJwtResourceServerFlowAuthenticatesAndMapsScopes(): void
    {
        $now = new DateTimeImmutable('2026-08-07T20:00:00+00:00');

        $result = $this->bridge(
            $this->jwtValidator($now)
        )->authenticate(
            'Bearer header.payload.signature-material',
            $now
        );

        self::assertTrue(
            $result->authentication()->isAuthenticated()
        );

        $principal = $result->authentication()->principal();
        self::assertNotNull($principal);
        self::assertSame(
            'jwt-product-user',
            $principal->identity()->id()->value()
        );

        $context = $result->authorizationContext();
        self::assertNotNull($context);
        self::assertSame(
            ['invoice.read'],
            $context->permissions()->values()
        );
    }

    public function testOpaqueResourceServerFlowConvergesOnSameValidatedTokenModel(): void
    {
        $now = new DateTimeImmutable('2026-08-07T20:00:00+00:00');

        $result = $this->bridge(
            $this->opaqueValidator($now)
        )->authenticate(
            'Bearer opaque-token-material-abcdefghijklmnopqrstuvwxyz',
            $now
        );

        self::assertTrue(
            $result->authentication()->isAuthenticated()
        );

        $token = $result->authentication()->token();
        self::assertNotNull($token);
        self::assertSame(
            ValidatedAccessToken::class,
            $token::class
        );
        self::assertSame(
            ['invoice.read'],
            $token->scopes()->values()
        );
    }

    public function testUnknownScopeNeverBecomesApplicationPermissionImplicitly(): void
    {
        $map = new ScopePermissionMap([
            'invoice.read' => new PermissionSet([
                new PermissionIdentifier('invoice.read'),
            ]),
        ]);

        self::assertSame(
            [],
            $map->resolve(new ScopeSet(['unknown.scope']))->values()
        );
    }

    public function testJwksUnknownKidFailsClosedAfterResolutionAttempt(): void
    {
        $now = new DateTimeImmutable('2026-08-07T20:00:00+00:00');

        $parser = new class($now) implements JwtParserInterface {
            public function __construct(private DateTimeImmutable $now)
            {
            }

            public function parse(AccessToken $token): ParsedJwt
            {
                return new ParsedJwt(
                    new JwtHeader('RS256', 'missing-key', 'JWT'),
                    new JwtClaims(
                        'jwt-product-user',
                        'https://issuer.example',
                        ['sif-api'],
                        $this->now->modify('+1 hour'),
                        $this->now,
                        null,
                        'invoice.read'
                    ),
                    'header.payload',
                    'signature'
                );
            }
        };

        $crypto = new class implements JwkSignatureVerifierInterface {
            public function verify(
                ParsedJwt $jwt,
                Jwk $key
            ): bool {
                return true;
            }
        };

        $validator = new JwtAccessTokenValidator(
            $parser,
            new JwksJwtSignatureVerifier(
                new JwkResolver(
                    new InMemoryJwkSetProvider(
                        new JwkSet([
                            new Jwk(
                                'known-key',
                                'RSA',
                                'RS256',
                                ['n' => 'modulus', 'e' => 'AQAB']
                            ),
                        ])
                    )
                ),
                $crypto
            ),
            new JwtValidationPolicy(
                ['RS256'],
                'https://issuer.example',
                ['sif-api'],
                new DateInterval('PT30S')
            )
        );

        self::assertNull(
            $validator->validate(
                new AccessToken('header.payload.signature-material'),
                $now
            )
        );
    }

    public function testInactiveOpaqueTokenFailsClosed(): void
    {
        $validator = new OpaqueAccessTokenValidator(
            new class implements TokenIntrospectorInterface {
                public function introspect(
                    AccessToken $token
                ): TokenIntrospectionResult {
                    return new TokenIntrospectionResult(false);
                }
            }
        );

        self::assertNull(
            $validator->validate(
                new AccessToken(
                    'opaque-token-material-abcdefghijklmnopqrstuvwxyz'
                ),
                new DateTimeImmutable('2026-08-07T20:00:00+00:00')
            )
        );
    }

    public function testProductDoesNotIssueTokensOrCreateSessionState(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/OAuth2';

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        ) as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            self::assertIsString($source);
            self::assertStringNotContainsString('AuthorizationCode', $source);
            self::assertStringNotContainsString('RefreshToken', $source);
            self::assertStringNotContainsString('issueToken', $source);
            self::assertStringNotContainsString('setcookie(', strtolower($source));
        }
    }

    private function bridge(
        AccessTokenValidatorInterface $validator
    ): ResourceServerApiBridge {
        return new ResourceServerApiBridge(
            new OAuth2ResourceServerAuthenticator(
                new BearerTokenExtractor(),
                $validator,
                new BearerPrincipalFactory(),
                new BearerAuthenticationFailureFactory(),
                'sif-api'
            ),
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

    private function jwtValidator(
        DateTimeImmutable $now
    ): JwtAccessTokenValidator {
        $parser = new class($now) implements JwtParserInterface {
            public function __construct(private DateTimeImmutable $now)
            {
            }

            public function parse(AccessToken $token): ParsedJwt
            {
                return new ParsedJwt(
                    new JwtHeader('RS256', 'key-1', 'JWT'),
                    new JwtClaims(
                        'jwt-product-user',
                        'https://issuer.example',
                        ['sif-api'],
                        $this->now->modify('+1 hour'),
                        $this->now,
                        null,
                        'invoice.read',
                        ['client_id' => 'jwt-client']
                    ),
                    'header.payload',
                    'signature'
                );
            }
        };

        $crypto = new class implements JwkSignatureVerifierInterface {
            public function verify(
                ParsedJwt $jwt,
                Jwk $key
            ): bool {
                return true;
            }
        };

        return new JwtAccessTokenValidator(
            $parser,
            new JwksJwtSignatureVerifier(
                new JwkResolver(
                    new InMemoryJwkSetProvider(
                        new JwkSet([
                            new Jwk(
                                'key-1',
                                'RSA',
                                'RS256',
                                ['n' => 'modulus', 'e' => 'AQAB']
                            ),
                        ])
                    )
                ),
                $crypto
            ),
            new JwtValidationPolicy(
                ['RS256'],
                'https://issuer.example',
                ['sif-api'],
                new DateInterval('PT30S')
            )
        );
    }

    private function opaqueValidator(
        DateTimeImmutable $now
    ): OpaqueAccessTokenValidator {
        return new OpaqueAccessTokenValidator(
            new class($now) implements TokenIntrospectorInterface {
                public function __construct(
                    private DateTimeImmutable $now
                ) {
                }

                public function introspect(
                    AccessToken $token
                ): TokenIntrospectionResult {
                    return new TokenIntrospectionResult(
                        true,
                        'opaque-product-user',
                        new ScopeSet(['invoice.read']),
                        $this->now->modify('+1 hour'),
                        $this->now,
                        ['client_id' => 'opaque-client']
                    );
                }
            }
        );
    }
}
