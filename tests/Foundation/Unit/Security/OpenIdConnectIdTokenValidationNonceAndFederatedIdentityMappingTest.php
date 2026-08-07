<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\JwtSignatureVerifierInterface;
use Sif\Foundation\Security\Contracts\OidcIdTokenParserInterface;
use Sif\Foundation\Security\OAuth2\Jwt\JwtClaims;
use Sif\Foundation\Security\OAuth2\Jwt\JwtHeader;
use Sif\Foundation\Security\OAuth2\Jwt\ParsedJwt;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Oidc\OidcIdToken;
use Sif\Foundation\Security\Oidc\OidcIdTokenValidationPolicy;
use Sif\Foundation\Security\Oidc\OidcIdTokenValidator;
use Sif\Foundation\Security\Oidc\OidcNonce;

final class OpenIdConnectIdTokenValidationNonceAndFederatedIdentityMappingTest extends TestCase
{
    public function testValidIdTokenProducesFederatedIdentity(): void
    {
        $now = new DateTimeImmutable('2026-08-07T20:00:00+00:00');

        $result = $this->validator(
            $this->jwt(
                $now,
                'https://identity.example',
                ['sif-client'],
                'abcdefghijklmnopqrstuvwxyz0123456789_NONCE'
            ),
            true
        )->validate(
            new OidcIdToken('header.payload.signature-material'),
            new OidcNonce(
                'abcdefghijklmnopqrstuvwxyz0123456789_NONCE'
            ),
            $now
        );

        self::assertNotNull($result);
        self::assertSame(
            'federated-user',
            $result->identity()->subject()
        );
        self::assertSame(
            'https://identity.example',
            $result->identity()->issuer()
        );
        self::assertSame(
            'user@example.test',
            $result->identity()->claims()['email']
        );
    }

    public function testNonceMismatchFailsClosed(): void
    {
        $now = new DateTimeImmutable('2026-08-07T20:00:00+00:00');

        self::assertNull(
            $this->validator(
                $this->jwt(
                    $now,
                    'https://identity.example',
                    ['sif-client'],
                    'differentabcdefghijklmnopqrstuvwxyz_NONCE'
                ),
                true
            )->validate(
                new OidcIdToken('header.payload.signature-material'),
                new OidcNonce(
                    'abcdefghijklmnopqrstuvwxyz0123456789_NONCE'
                ),
                $now
            )
        );
    }

    public function testWrongIssuerAudienceAlgorithmOrSignatureFailsClosed(): void
    {
        $now = new DateTimeImmutable('2026-08-07T20:00:00+00:00');
        $nonce = new OidcNonce(
            'abcdefghijklmnopqrstuvwxyz0123456789_NONCE'
        );
        $token = new OidcIdToken(
            'header.payload.signature-material'
        );

        self::assertNull(
            $this->validator(
                $this->jwt(
                    $now,
                    'https://wrong.example',
                    ['sif-client'],
                    $nonce->value()
                ),
                true
            )->validate($token, $nonce, $now)
        );

        self::assertNull(
            $this->validator(
                $this->jwt(
                    $now,
                    'https://identity.example',
                    ['other-client'],
                    $nonce->value()
                ),
                true
            )->validate($token, $nonce, $now)
        );

        self::assertNull(
            $this->validator(
                $this->jwt(
                    $now,
                    'https://identity.example',
                    ['sif-client'],
                    $nonce->value(),
                    'none'
                ),
                true
            )->validate($token, $nonce, $now)
        );

        self::assertNull(
            $this->validator(
                $this->jwt(
                    $now,
                    'https://identity.example',
                    ['sif-client'],
                    $nonce->value()
                ),
                false
            )->validate($token, $nonce, $now)
        );
    }

    public function testExpiredOrFutureIdTokenFailsClosed(): void
    {
        $now = new DateTimeImmutable('2026-08-07T20:00:00+00:00');
        $nonce = new OidcNonce(
            'abcdefghijklmnopqrstuvwxyz0123456789_NONCE'
        );
        $token = new OidcIdToken(
            'header.payload.signature-material'
        );

        $expired = new ParsedJwt(
            new JwtHeader('RS256', 'key-1', 'JWT'),
            new JwtClaims(
                'federated-user',
                'https://identity.example',
                ['sif-client'],
                $now->modify('-1 minute'),
                $now->modify('-1 hour'),
                null,
                null,
                ['nonce' => $nonce->value()]
            ),
            'header.payload',
            'signature'
        );

        $future = new ParsedJwt(
            new JwtHeader('RS256', 'key-1', 'JWT'),
            new JwtClaims(
                'federated-user',
                'https://identity.example',
                ['sif-client'],
                $now->modify('+1 hour'),
                $now->modify('+10 minutes'),
                null,
                null,
                ['nonce' => $nonce->value()]
            ),
            'header.payload',
            'signature'
        );

        self::assertNull(
            $this->validator($expired, true)->validate(
                $token,
                $nonce,
                $now
            )
        );

        self::assertNull(
            $this->validator($future, true)->validate(
                $token,
                $nonce,
                $now
            )
        );
    }

    public function testFederatedIdentityKeyIsStableAcrossMutableClaims(): void
    {
        $first = new OidcFederatedIdentity(
            'https://identity.example',
            'subject-123',
            ['email' => 'first@example.test']
        );

        $second = new OidcFederatedIdentity(
            'https://identity.example',
            'subject-123',
            ['email' => 'changed@example.test']
        );

        self::assertSame(
            $first->stableKey(),
            $second->stableKey()
        );
    }

    public function testIdTokenIsRedactedAndValidationDoesNotCreateSession(): void
    {
        $token = new OidcIdToken(
            'header.payload.signature-material'
        );

        self::assertSame('[REDACTED]', (string) $token);

        $reflection = new \ReflectionClass(
            OidcIdTokenValidator::class
        );
        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('Session', $source);
        self::assertStringNotContainsString('setcookie(', strtolower($source));
        self::assertStringNotContainsString('Keycloak', $source);
    }

    private function validator(
        ParsedJwt $jwt,
        bool $signatureValid
    ): OidcIdTokenValidator {
        $parser = new class($jwt) implements OidcIdTokenParserInterface {
            public function __construct(
                private ParsedJwt $jwt
            ) {
            }

            public function parse(
                OidcIdToken $idToken
            ): ParsedJwt {
                return $this->jwt;
            }
        };

        $signatureVerifier = new class($signatureValid) implements JwtSignatureVerifierInterface {
            public function __construct(
                private bool $valid
            ) {
            }

            public function verify(
                ParsedJwt $jwt
            ): bool {
                return $this->valid;
            }
        };

        return new OidcIdTokenValidator(
            $parser,
            $signatureVerifier,
            new OidcIdTokenValidationPolicy(
                'https://identity.example',
                'sif-client',
                ['RS256'],
                new DateInterval('PT30S')
            )
        );
    }

    /**
     * @param list<string> $audiences
     */
    private function jwt(
        DateTimeImmutable $now,
        string $issuer,
        array $audiences,
        string $nonce,
        string $algorithm = 'RS256'
    ): ParsedJwt {
        return new ParsedJwt(
            new JwtHeader($algorithm, 'key-1', 'JWT'),
            new JwtClaims(
                'federated-user',
                $issuer,
                $audiences,
                $now->modify('+1 hour'),
                $now->modify('-1 minute'),
                null,
                null,
                [
                    'nonce' => $nonce,
                    'email' => 'user@example.test',
                    'email_verified' => true,
                ]
            ),
            'header.payload',
            'signature'
        );
    }
}
