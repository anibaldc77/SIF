<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\SecurityEventTokenAudienceValidatorInterface;
use Sif\Foundation\Security\Contracts\SecurityEventTokenIssuerValidatorInterface;
use Sif\Foundation\Security\Contracts\SecurityEventTokenReplayStoreInterface;
use Sif\Foundation\Security\Contracts\SecurityEventTokenTimeValidatorInterface;
use Sif\Foundation\Security\Contracts\SecurityEventTokenVerifierInterface;
use Sif\Foundation\Security\SharedSignals\SecurityEvent;
use Sif\Foundation\Security\SharedSignals\SecurityEventSubject;
use Sif\Foundation\Security\SharedSignals\SecurityEventToken;
use Sif\Foundation\Security\SharedSignals\SecurityEventTokenValidationContext;
use Sif\Foundation\Security\SharedSignals\SecurityEventTokenValidationResult;

final class SecurityEventTokenVerificationIssuerAudienceTimeAndReplayBoundariesTest extends TestCase
{
    public function testValidationContextKeepsExpectedTrustInputsExplicit(): void
    {
        $context = new SecurityEventTokenValidationContext(
            'https://issuer.example.test',
            'https://receiver.example.test',
            new DateTimeImmutable('2026-08-10T12:00:00Z'),
            30
        );

        self::assertSame(
            'https://issuer.example.test',
            $context->expectedIssuer()
        );
        self::assertSame(
            'https://receiver.example.test',
            $context->expectedAudience()
        );
        self::assertSame(30, $context->allowedClockSkewSeconds());
    }

    public function testValidationResultAggregatesSecurityChecks(): void
    {
        $result = new SecurityEventTokenValidationResult(
            $this->token(),
            true,
            true,
            true,
            true
        );

        self::assertTrue($result->issuerValid());
        self::assertTrue($result->audienceValid());
        self::assertTrue($result->timeValid());
        self::assertTrue($result->replaySafe());
        self::assertTrue($result->valid());
    }

    public function testInvalidValidationResultIsNotAcceptedAsValid(): void
    {
        $result = new SecurityEventTokenValidationResult(
            $this->token(),
            true,
            true,
            true,
            false,
            ['replay detected']
        );

        self::assertFalse($result->valid());
        self::assertSame(['replay detected'], $result->warnings());
    }

    public function testExistingVerifierMethodRemainsBackwardCompatible(): void
    {
        $method = new \ReflectionMethod(
            SecurityEventTokenVerifierInterface::class,
            'verify'
        );

        self::assertSame(
            SecurityEventToken::class,
            (string) $method->getReturnType()
        );
    }

    public function testContextualVerifierReturnsTypedValidationResult(): void
    {
        $method = new \ReflectionMethod(
            SecurityEventTokenVerifierInterface::class,
            'verifyWithContext'
        );

        self::assertSame(
            SecurityEventTokenValidationResult::class,
            (string) $method->getReturnType()
        );
    }

    public function testIssuerAudienceTimeAndReplayRemainSeparateContracts(): void
    {
        foreach ([
            SecurityEventTokenIssuerValidatorInterface::class,
            SecurityEventTokenAudienceValidatorInterface::class,
            SecurityEventTokenTimeValidatorInterface::class,
            SecurityEventTokenReplayStoreInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testVerificationLayerRemainsCryptoAndStorageNeutral(): void
    {
        foreach ([
            SecurityEventTokenVerifierInterface::class,
            SecurityEventTokenIssuerValidatorInterface::class,
            SecurityEventTokenAudienceValidatorInterface::class,
            SecurityEventTokenTimeValidatorInterface::class,
            SecurityEventTokenReplayStoreInterface::class,
            SecurityEventTokenValidationContext::class,
            SecurityEventTokenValidationResult::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('firebase', strtolower($source));
            self::assertStringNotContainsString('lcobucci', strtolower($source));
        }
    }

    private function token(): SecurityEventToken
    {
        return new SecurityEventToken(
            'https://issuer.example.test',
            'set-001',
            new DateTimeImmutable('2026-08-10T12:00:00Z'),
            [
                new SecurityEvent(
                    'session-revoked',
                    new SecurityEventSubject(
                        'account',
                        [
                            'iss' => 'https://issuer.example.test',
                            'sub' => 'user-001',
                        ]
                    ),
                    new DateTimeImmutable('2026-08-10T11:59:59Z')
                ),
            ]
        );
    }
}
