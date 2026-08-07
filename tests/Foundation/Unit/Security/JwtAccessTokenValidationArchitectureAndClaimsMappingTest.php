<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\JwtParserInterface;
use Sif\Foundation\Security\Contracts\JwtSignatureVerifierInterface;
use Sif\Foundation\Security\OAuth2\AccessToken;
use Sif\Foundation\Security\OAuth2\Jwt\JwtAccessTokenValidator;
use Sif\Foundation\Security\OAuth2\Jwt\JwtClaims;
use Sif\Foundation\Security\OAuth2\Jwt\JwtClaimsMapper;
use Sif\Foundation\Security\OAuth2\Jwt\JwtHeader;
use Sif\Foundation\Security\OAuth2\Jwt\JwtValidationPolicy;
use Sif\Foundation\Security\OAuth2\Jwt\ParsedJwt;

final class JwtAccessTokenValidationArchitectureAndClaimsMappingTest extends TestCase
{
    public function testClaimsMapperMapsStandardClaimsAndScalarAdditionalClaims(): void
    {
        $mapped = (new JwtClaimsMapper())->map([
            'sub' => 'jwt-user',
            'iss' => 'https://issuer.example',
            'aud' => ['sif-api', 'secondary-api'],
            'exp' => 1786129200,
            'iat' => 1786125600,
            'nbf' => 1786125600,
            'scope' => 'api.read invoice.read',
            'client_id' => 'client-1',
            'tenant.id' => 7,
            'nested' => ['not' => 'copied'],
        ]);

        self::assertSame('jwt-user', $mapped->subject());
        self::assertSame(
            ['sif-api', 'secondary-api'],
            $mapped->audiences()
        );
        self::assertSame('client-1', $mapped->additional()['client_id']);
        self::assertSame(7, $mapped->additional()['tenant.id']);
        self::assertArrayNotHasKey('nested', $mapped->additional());
    }

    public function testValidatorAcceptsSignedTokenWithAllowedAlgorithmIssuerAudienceAndTime(): void
    {
        $now = new DateTimeImmutable('2026-08-07T16:00:00+00:00');
        $validator = $this->validator(
            $this->jwt(
                'RS256',
                'https://issuer.example',
                ['sif-api'],
                $now->modify('+1 hour'),
                $now->modify('-1 minute')
            ),
            true
        );

        $validated = $validator->validate(
            new AccessToken('header.payload.signature-material'),
            $now
        );

        self::assertNotNull($validated);
        self::assertSame('jwt-user', $validated->subject()->value());
        self::assertSame(
            ['api.read', 'invoice.read'],
            $validated->scopes()->values()
        );
    }

    public function testValidatorRejectsDisallowedAlgorithmBeforeTrustingClaims(): void
    {
        $now = new DateTimeImmutable('2026-08-07T16:00:00+00:00');

        $validated = $this->validator(
            $this->jwt(
                'none',
                'https://issuer.example',
                ['sif-api'],
                $now->modify('+1 hour'),
                $now
            ),
            true
        )->validate(
            new AccessToken('header.payload.signature-material'),
            $now
        );

        self::assertNull($validated);
    }

    public function testValidatorRejectsInvalidSignature(): void
    {
        $now = new DateTimeImmutable('2026-08-07T16:00:00+00:00');

        $validated = $this->validator(
            $this->jwt(
                'RS256',
                'https://issuer.example',
                ['sif-api'],
                $now->modify('+1 hour'),
                $now
            ),
            false
        )->validate(
            new AccessToken('header.payload.signature-material'),
            $now
        );

        self::assertNull($validated);
    }

    public function testValidatorRejectsExpiredWrongIssuerOrWrongAudience(): void
    {
        $now = new DateTimeImmutable('2026-08-07T16:00:00+00:00');

        foreach ([
            $this->jwt(
                'RS256',
                'https://issuer.example',
                ['sif-api'],
                $now->modify('-10 minutes'),
                $now->modify('-1 hour')
            ),
            $this->jwt(
                'RS256',
                'https://wrong-issuer.example',
                ['sif-api'],
                $now->modify('+1 hour'),
                $now
            ),
            $this->jwt(
                'RS256',
                'https://issuer.example',
                ['other-api'],
                $now->modify('+1 hour'),
                $now
            ),
        ] as $jwt) {
            self::assertNull(
                $this->validator($jwt, true)->validate(
                    new AccessToken('header.payload.signature-material'),
                    $now
                )
            );
        }
    }

    public function testValidationArchitectureDoesNotFetchKeysOrAssumeJwks(): void
    {
        foreach ([
            JwtParserInterface::class,
            JwtSignatureVerifierInterface::class,
        ] as $contract) {
            $reflection = new \ReflectionClass($contract);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('JWKS', $source);
            self::assertStringNotContainsString('HttpClient', $source);
            self::assertStringNotContainsString('curl', strtolower($source));
            self::assertStringNotContainsString('Keycloak', $source);
        }
    }

    private function validator(
        ParsedJwt $jwt,
        bool $signatureValid
    ): JwtAccessTokenValidator {
        $parser = new class($jwt) implements JwtParserInterface {
            public function __construct(private ParsedJwt $jwt)
            {
            }

            public function parse(AccessToken $token): ParsedJwt
            {
                return $this->jwt;
            }
        };

        $verifier = new class($signatureValid) implements JwtSignatureVerifierInterface {
            public function __construct(private bool $valid)
            {
            }

            public function verify(ParsedJwt $jwt): bool
            {
                return $this->valid;
            }
        };

        return new JwtAccessTokenValidator(
            $parser,
            $verifier,
            new JwtValidationPolicy(
                ['RS256'],
                'https://issuer.example',
                ['sif-api'],
                new DateInterval('PT30S')
            )
        );
    }

    /**
     * @param list<string> $audiences
     */
    private function jwt(
        string $algorithm,
        string $issuer,
        array $audiences,
        DateTimeImmutable $expiresAt,
        DateTimeImmutable $issuedAt
    ): ParsedJwt {
        return new ParsedJwt(
            new JwtHeader($algorithm, 'key-1', 'JWT'),
            new JwtClaims(
                'jwt-user',
                $issuer,
                $audiences,
                $expiresAt,
                $issuedAt,
                $issuedAt,
                'api.read invoice.read',
                ['client_id' => 'client-1']
            ),
            'encoded-header.encoded-payload',
            'binary-signature'
        );
    }
}
