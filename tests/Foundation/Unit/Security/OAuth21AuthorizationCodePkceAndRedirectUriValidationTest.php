<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthAuthorizationCodeRepositoryInterface;
use Sif\Foundation\Security\OAuth\AuthorizationServer\DefaultOAuthPkceVerifier;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationCode;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientId;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthPkceChallenge;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthPkceMethod;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthPkceVerifier;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRedirectUri;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthRedirectUriValidator;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthScope;
use Sif\Foundation\Security\Exceptions\InvalidOAuthRedirectUriException;

final class OAuth21AuthorizationCodePkceAndRedirectUriValidationTest extends TestCase
{
    public function testAuthorizationCodeCarriesClientRedirectScopesAndLifetime(): void
    {
        $code = new OAuthAuthorizationCode(
            'code-001',
            new OAuthClientId('client-001'),
            new OAuthRedirectUri('https://example.test/callback'),
            [new OAuthScope('profile')],
            new DateTimeImmutable('2026-08-08T10:00:00Z'),
            new DateTimeImmutable('2026-08-08T10:05:00Z'),
            'challenge',
            OAuthPkceMethod::S256
        );

        self::assertSame('code-001', $code->value());
        self::assertFalse(
            $code->expiredAt(
                new DateTimeImmutable('2026-08-08T10:04:59Z')
            )
        );
        self::assertTrue(
            $code->expiredAt(
                new DateTimeImmutable('2026-08-08T10:05:00Z')
            )
        );
    }

    public function testPkceS256VerificationMatchesRfcStyleTransformation(): void
    {
        $verifier = new OAuthPkceVerifier(
            'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk'
        );

        $challenge = new OAuthPkceChallenge(
            'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
            new OAuthPkceMethod(OAuthPkceMethod::S256)
        );

        self::assertTrue(
            (new DefaultOAuthPkceVerifier())
                ->verify($verifier, $challenge)
        );
    }

    public function testPkceVerificationRejectsWrongVerifier(): void
    {
        $challenge = new OAuthPkceChallenge(
            'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
            new OAuthPkceMethod(OAuthPkceMethod::S256)
        );

        self::assertFalse(
            (new DefaultOAuthPkceVerifier())->verify(
                new OAuthPkceVerifier(
                    'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
                ),
                $challenge
            )
        );
    }

    public function testRedirectUriMustMatchRegisteredValueExactly(): void
    {
        $client = new OAuthClient(
            new OAuthClientId('client-001'),
            'Example',
            false,
            [
                new OAuthRedirectUri(
                    'https://example.test/callback'
                ),
            ],
            [new OAuthScope('profile')]
        );

        (new OAuthRedirectUriValidator())->assertAllowed(
            $client,
            new OAuthRedirectUri(
                'https://example.test/callback'
            )
        );

        self::addToAssertionCount(1);
    }

    public function testRedirectUriMismatchIsRejected(): void
    {
        $client = new OAuthClient(
            new OAuthClientId('client-001'),
            'Example',
            false,
            [
                new OAuthRedirectUri(
                    'https://example.test/callback'
                ),
            ],
            [new OAuthScope('profile')]
        );

        $this->expectException(
            InvalidOAuthRedirectUriException::class
        );

        (new OAuthRedirectUriValidator())->assertAllowed(
            $client,
            new OAuthRedirectUri(
                'https://attacker.test/callback'
            )
        );
    }

    public function testAuthorizationCodeRepositoryRemainsStorageNeutral(): void
    {
        $reflection = new \ReflectionClass(
            OAuthAuthorizationCodeRepositoryInterface::class
        );

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('PDO', $source);
        self::assertStringNotContainsString('Redis', $source);
        self::assertStringNotContainsString('curl_', strtolower($source));
    }
}
