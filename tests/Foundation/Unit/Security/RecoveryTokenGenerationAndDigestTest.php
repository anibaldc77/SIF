<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\RecoveryTokenGeneratorInterface;
use Sif\Foundation\Security\Exceptions\InvalidRecoveryTokenException;
use Sif\Foundation\Security\Recovery\CryptographicRecoveryTokenGenerator;
use Sif\Foundation\Security\Recovery\RecoveryToken;
use Sif\Foundation\Security\Recovery\RecoveryTokenDigest;

final class RecoveryTokenGenerationAndDigestTest extends TestCase
{
    public function testGeneratorProducesDistinctUrlSafeTokensWithMinimumEntropy(): void
    {
        $generator = new CryptographicRecoveryTokenGenerator();
        $first = $generator->generate();
        $second = $generator->generate();

        self::assertInstanceOf(RecoveryTokenGeneratorInterface::class, $generator);
        self::assertGreaterThanOrEqual(43, $first->length());
        self::assertNotSame(
            $first->expose(static fn (string $value): string => $value),
            $second->expose(static fn (string $value): string => $value)
        );
        self::assertMatchesRegularExpression(
            '/^[A-Za-z0-9_-]+$/',
            $first->expose(static fn (string $value): string => $value)
        );
    }

    public function testTokenIsRedactedAndCannotBeSerializedOrCloned(): void
    {
        $token = new RecoveryToken(str_repeat('A', 43));

        self::assertSame('[REDACTED]', (string) $token);
        self::assertSame('[REDACTED]', $token->__debugInfo()['value']);

        try {
            serialize($token);
            self::fail('Recovery token serialization must fail.');
        } catch (InvalidRecoveryTokenException $exception) {
            self::assertStringNotContainsString(str_repeat('A', 43), $exception->getMessage());
        }

        $this->expectException(InvalidRecoveryTokenException::class);
        clone $token;
    }

    public function testDigestIsDeterministicPersistableAndMatchesInConstantTimeBoundary(): void
    {
        $token = new RecoveryToken(str_repeat('B', 43));
        $sameToken = new RecoveryToken(str_repeat('B', 43));
        $differentToken = new RecoveryToken(str_repeat('C', 43));
        $digest = RecoveryTokenDigest::fromToken($token);

        self::assertSame('sha256', $digest->algorithm());
        self::assertSame(64, strlen($digest->value()));
        self::assertTrue($digest->matches($sameToken));
        self::assertFalse($digest->matches($differentToken));
        self::assertTrue($digest->equals(new RecoveryTokenDigest($digest->value())));
    }

    public function testInvalidTokensDigestsAndEntropyPoliciesFailClosed(): void
    {
        foreach (
            [
                static fn (): object => new RecoveryToken('too-short'),
                static fn (): object => new RecoveryToken(str_repeat('A', 31) . '+'),
                static fn (): object => new RecoveryTokenDigest('not-a-digest'),
                static fn (): object => new CryptographicRecoveryTokenGenerator(16),
            ] as $factory
        ) {
            try {
                $factory();
                self::fail('Invalid recovery token material must be rejected.');
            } catch (InvalidRecoveryTokenException) {
                self::addToAssertionCount(1);
            }
        }
    }
}
