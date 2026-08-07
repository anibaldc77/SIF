<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Exceptions\InvalidBearerTokenException;
use Sif\Foundation\Security\OAuth2\BearerAuthenticationFailureFactory;
use Sif\Foundation\Security\OAuth2\BearerErrorCode;
use Sif\Foundation\Security\OAuth2\BearerTokenExtractor;

final class BearerTokenExtractionAndRfc6750ErrorModelTest extends TestCase
{
    public function testBearerTokenIsExtractedCaseInsensitively(): void
    {
        $extractor = new BearerTokenExtractor();

        $token = $extractor->extract(
            'bEaReR opaque-token-material-abcdefghijklmnopqrstuvwxyz'
        );

        self::assertNotNull($token);
        self::assertSame(
            'opaque-token-material-abcdefghijklmnopqrstuvwxyz',
            $token->expose(static fn (string $raw): string => $raw)
        );
    }

    public function testEmptyAuthorizationHeaderMeansNoCredential(): void
    {
        self::assertNull(
            (new BearerTokenExtractor())->extract('   ')
        );
    }

    public function testMalformedAuthorizationHeaderFailsExplicitly(): void
    {
        $this->expectException(InvalidBearerTokenException::class);

        (new BearerTokenExtractor())->extract(
            'Basic opaque-token-material-abcdefghijklmnopqrstuvwxyz'
        );
    }

    public function testInvalidTokenFailureUses401AndWwwAuthenticateChallenge(): void
    {
        $failure = (new BearerAuthenticationFailureFactory())->invalidToken(
            'sif-api',
            'The access token is invalid or expired.'
        );

        self::assertSame(401, $failure->statusCode());
        self::assertSame(
            BearerErrorCode::InvalidToken,
            $failure->error()->code()
        );
        self::assertStringContainsString(
            'Bearer realm="sif-api"',
            $failure->challenge()->headerValue()
        );
        self::assertStringContainsString(
            'error="invalid_token"',
            $failure->challenge()->headerValue()
        );
    }

    public function testInsufficientScopeUses403AndScopeHint(): void
    {
        $failure = (new BearerAuthenticationFailureFactory())->insufficientScope(
            'sif-api',
            'invoice.write',
            'Additional scope is required.'
        );

        self::assertSame(403, $failure->statusCode());
        self::assertSame(
            BearerErrorCode::InsufficientScope,
            $failure->error()->code()
        );
        self::assertSame('invoice.write', $failure->error()->scope());
        self::assertStringContainsString(
            'scope="invoice.write"',
            $failure->challenge()->headerValue()
        );
    }

    public function testBearerErrorModelNeverExposesAccessTokenMaterial(): void
    {
        $factory = new BearerAuthenticationFailureFactory();

        foreach ([
            $factory->invalidRequest('sif-api', 'Malformed request.'),
            $factory->invalidToken('sif-api', 'Invalid token.'),
            $factory->insufficientScope(
                'sif-api',
                'api.write',
                'Insufficient scope.'
            ),
        ] as $failure) {
            $encoded = json_encode(
                $failure->error()->toArray(),
                JSON_THROW_ON_ERROR
            );

            self::assertStringNotContainsString(
                'opaque-token-material',
                $encoded
            );
            self::assertStringNotContainsString(
                'Authorization:',
                $encoded
            );
        }
    }
}
