<?php
declare(strict_types=1);
namespace Sif\Tests\Foundation\Unit\Security;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OAuthSenderConstrainedAccessTokenIssuerInterface;
use Sif\Foundation\Security\Contracts\OAuthSenderConstraintValidatorInterface;
use Sif\Foundation\Security\Contracts\OAuthTokenConfirmationExtractorInterface;
use Sif\Foundation\Security\OAuth\Advanced\OAuthDPoPVerificationResult;
use Sif\Foundation\Security\OAuth\Advanced\OAuthSenderConstrainedAccessToken;
use Sif\Foundation\Security\OAuth\Advanced\OAuthSenderConstraintValidationResult;
use Sif\Foundation\Security\OAuth\Advanced\OAuthTokenConfirmation;

final class OAuthSenderConstrainedAccessTokensAndDPoPTokenBindingContractsTest extends TestCase
{
    public function testConfirmationCarriesPublicKeyThumbprint(): void
    {
        $confirmation = new OAuthTokenConfirmation('thumb-001');
        self::assertSame('thumb-001', $confirmation->publicKeyThumbprint());
    }

    public function testSenderConstrainedTokenCarriesConfirmation(): void
    {
        $token = new OAuthSenderConstrainedAccessToken(
            'access-token',
            new OAuthTokenConfirmation('thumb-001'),
            new DateTimeImmutable('2026-08-09T11:00:00Z')
        );
        self::assertSame('access-token', $token->serializedToken());
        self::assertSame('thumb-001', $token->confirmation()->publicKeyThumbprint());
    }

    public function testBindingResultDetectsMatchingThumbprints(): void
    {
        $result = new OAuthSenderConstraintValidationResult('thumb-001', 'thumb-001');
        self::assertTrue($result->matches());
        self::assertSame('thumb-001', $result->tokenPublicKeyThumbprint());
    }

    public function testBindingResultDetectsMismatch(): void
    {
        $result = new OAuthSenderConstraintValidationResult('thumb-001', 'thumb-002');
        self::assertFalse($result->matches());
    }

    public function testContractsAreTypedAcrossIssuanceAndValidation(): void
    {
        $issue = new \ReflectionMethod(OAuthSenderConstrainedAccessTokenIssuerInterface::class, 'issue');
        $validate = new \ReflectionMethod(OAuthSenderConstraintValidatorInterface::class, 'validate');
        $extract = new \ReflectionMethod(OAuthTokenConfirmationExtractorInterface::class, 'extract');
        self::assertSame(OAuthSenderConstrainedAccessToken::class, (string) $issue->getReturnType());
        self::assertSame(OAuthSenderConstraintValidationResult::class, (string) $validate->getReturnType());
        self::assertSame(OAuthTokenConfirmation::class, (string) $extract->getReturnType());
    }

    public function testTokenBindingConnectsToValidatedDpopIdentityWithoutInfrastructureCoupling(): void
    {
        $proof = new OAuthDPoPVerificationResult(
            'thumb-001',
            'jti-001',
            new DateTimeImmutable('2026-08-09T10:00:00Z')
        );
        self::assertSame('thumb-001', $proof->publicKeyThumbprint());

        foreach ([
            OAuthSenderConstrainedAccessTokenIssuerInterface::class,
            OAuthSenderConstraintValidatorInterface::class,
            OAuthTokenConfirmationExtractorInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());
            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
        }
    }
}
