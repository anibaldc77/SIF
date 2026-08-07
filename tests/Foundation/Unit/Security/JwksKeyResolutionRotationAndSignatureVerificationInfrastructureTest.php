<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\JwkSetProviderInterface;
use Sif\Foundation\Security\Contracts\JwkSignatureVerifierInterface;
use Sif\Foundation\Security\OAuth2\Jwks\InMemoryJwkSetProvider;
use Sif\Foundation\Security\OAuth2\Jwks\Jwk;
use Sif\Foundation\Security\OAuth2\Jwks\JwkResolver;
use Sif\Foundation\Security\OAuth2\Jwks\JwkSet;
use Sif\Foundation\Security\OAuth2\Jwks\JwksJwtSignatureVerifier;
use Sif\Foundation\Security\OAuth2\Jwt\JwtClaims;
use Sif\Foundation\Security\OAuth2\Jwt\JwtHeader;
use Sif\Foundation\Security\OAuth2\Jwt\ParsedJwt;

final class JwksKeyResolutionRotationAndSignatureVerificationInfrastructureTest extends TestCase
{
    public function testResolverFindsExistingKeyWithoutRefresh(): void
    {
        $provider = new class($this->set('key-1')) implements JwkSetProviderInterface {
            public int $refreshCalls = 0;

            public function __construct(private JwkSet $set)
            {
            }

            public function get(): JwkSet
            {
                return $this->set;
            }

            public function refresh(): JwkSet
            {
                $this->refreshCalls++;

                return $this->set;
            }
        };

        $key = (new JwkResolver($provider))->resolve('key-1');

        self::assertNotNull($key);
        self::assertSame('key-1', $key->keyId());
        self::assertSame(0, $provider->refreshCalls);
    }

    public function testResolverRefreshesOnceForUnknownKidAndSupportsRotation(): void
    {
        $provider = new class($this->set('key-1')) implements JwkSetProviderInterface {
            public int $refreshCalls = 0;

            public function __construct(private JwkSet $set)
            {
            }

            public function get(): JwkSet
            {
                return $this->set;
            }

            public function refresh(): JwkSet
            {
                $this->refreshCalls++;
                $this->set = new JwkSet([
                    new Jwk(
                        'key-2',
                        'RSA',
                        'RS256',
                        ['n' => 'modulus-2', 'e' => 'AQAB']
                    ),
                ]);

                return $this->set;
            }
        };

        $key = (new JwkResolver($provider))->resolve('key-2');

        self::assertNotNull($key);
        self::assertSame('key-2', $key->keyId());
        self::assertSame(1, $provider->refreshCalls);
    }

    public function testMissingKidFailsClosed(): void
    {
        $verifier = new JwksJwtSignatureVerifier(
            new JwkResolver(new InMemoryJwkSetProvider($this->set('key-1'))),
            $this->acceptingVerifier()
        );

        self::assertFalse(
            $verifier->verify($this->jwt(null, 'RS256'))
        );
    }

    public function testAlgorithmMismatchFailsBeforeCryptographicVerification(): void
    {
        $calls = 0;

        $crypto = new class($calls) implements JwkSignatureVerifierInterface {
            public function __construct(private int &$calls)
            {
            }

            public function verify(ParsedJwt $jwt, Jwk $key): bool
            {
                $this->calls++;

                return true;
            }
        };

        $verifier = new JwksJwtSignatureVerifier(
            new JwkResolver(new InMemoryJwkSetProvider($this->set('key-1'))),
            $crypto
        );

        self::assertFalse(
            $verifier->verify($this->jwt('key-1', 'ES256'))
        );
        self::assertSame(0, $calls);
    }

    public function testResolvedJwkIsDelegatedToCryptographicVerifier(): void
    {
        $crypto = new class implements JwkSignatureVerifierInterface {
            private ?string $seenKey = null;

            public function verify(ParsedJwt $jwt, Jwk $key): bool
            {
                $this->seenKey = $key->keyId();

                return true;
            }

            public function seenKey(): ?string
            {
                return $this->seenKey;
            }
        };

        $verifier = new JwksJwtSignatureVerifier(
            new JwkResolver(new InMemoryJwkSetProvider($this->set('key-1'))),
            $crypto
        );

        self::assertTrue(
            $verifier->verify($this->jwt('key-1', 'RS256'))
        );
        self::assertSame('key-1', $crypto->seenKey());
    }

    public function testJwksInfrastructureDoesNotPerformHttpItself(): void
    {
        foreach ([
            JwkResolver::class,
            JwksJwtSignatureVerifier::class,
            InMemoryJwkSetProvider::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('curl', strtolower($source));
            self::assertStringNotContainsString('HttpClient', $source);
            self::assertStringNotContainsString('file_get_contents("http', $source);
            self::assertStringNotContainsString('Keycloak', $source);
        }
    }

    private function set(string $keyId): JwkSet
    {
        return new JwkSet([
            new Jwk(
                $keyId,
                'RSA',
                'RS256',
                ['n' => 'modulus', 'e' => 'AQAB']
            ),
        ]);
    }

    private function acceptingVerifier(): JwkSignatureVerifierInterface
    {
        return new class implements JwkSignatureVerifierInterface {
            public function verify(ParsedJwt $jwt, Jwk $key): bool
            {
                return true;
            }
        };
    }

    private function jwt(
        ?string $keyId,
        string $algorithm
    ): ParsedJwt {
        return new ParsedJwt(
            new JwtHeader($algorithm, $keyId, 'JWT'),
            new JwtClaims(
                'jwks-user',
                'https://issuer.example',
                ['sif-api'],
                new \DateTimeImmutable('2026-08-07T20:00:00+00:00'),
                new \DateTimeImmutable('2026-08-07T19:00:00+00:00'),
                null,
                'api.read'
            ),
            'encoded-header.encoded-payload',
            'binary-signature'
        );
    }
}
