<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\PasswordHasherInterface;
use Sif\Foundation\Security\Contracts\PasswordVerifierInterface;
use Sif\Foundation\Security\Exceptions\InvalidPasswordHashPolicyException;
use Sif\Foundation\Security\Password\Native\NativePasswordHasher;
use Sif\Foundation\Security\Password\Native\NativePasswordVerifier;
use Sif\Foundation\Security\Password\Native\PasswordHashPolicy;
use Sif\Foundation\Security\Password\PasswordCredential;
use Sif\Foundation\Security\Password\PasswordSecret;

final class NativePasswordHashingAndVerificationAdaptersTest extends TestCase
{
    public function testRuntimeDefaultPolicyHashesAndVerifiesPassword(): void
    {
        $policy = PasswordHashPolicy::runtimeDefault();
        $hasher = new NativePasswordHasher($policy);
        $verifier = new NativePasswordVerifier($policy);
        $secret = new PasswordSecret('correct horse battery staple');
        $storedHash = $hasher->hash($secret);

        $result = $verifier->verify(new PasswordCredential($secret), $storedHash);

        self::assertTrue($result->isVerified());
        self::assertFalse($result->requiresRehash());
        self::assertNotSame('unknown', $storedHash->algorithm()->value());
        self::assertStringNotContainsString('correct horse', print_r($storedHash, true));
    }

    public function testIncorrectPasswordIsRejectedWithoutRehashAdvice(): void
    {
        $policy = PasswordHashPolicy::bcrypt(10);
        $storedHash = (new NativePasswordHasher($policy))->hash(new PasswordSecret('expected-secret'));

        $result = (new NativePasswordVerifier($policy))->verify(
            new PasswordCredential(new PasswordSecret('incorrect-secret')),
            $storedHash
        );

        self::assertFalse($result->isVerified());
        self::assertFalse($result->requiresRehash());
    }

    public function testStrongerBcryptPolicyRequestsRehashAfterSuccessfulVerification(): void
    {
        $storedHash = (new NativePasswordHasher(PasswordHashPolicy::bcrypt(4)))
            ->hash(new PasswordSecret('rehash-me'));

        $result = (new NativePasswordVerifier(PasswordHashPolicy::bcrypt(5)))->verify(
            new PasswordCredential(new PasswordSecret('rehash-me')),
            $storedHash
        );

        self::assertTrue($result->isVerified());
        self::assertTrue($result->requiresRehash());
    }

    public function testInvalidNativeHashIsRejectedRatherThanProducingTechnicalFailure(): void
    {
        $storedHash = new \Sif\Foundation\Security\Password\StoredPasswordHash(
            new \Sif\Foundation\Security\Password\PasswordHashAlgorithm('legacy.unknown'),
            '$not-a-native-password-hash'
        );

        $result = (new NativePasswordVerifier(PasswordHashPolicy::runtimeDefault()))->verify(
            new PasswordCredential(new PasswordSecret('secret')),
            $storedHash
        );

        self::assertFalse($result->isVerified());
        self::assertFalse($result->requiresRehash());
    }

    public function testInvalidBcryptCostsAreRejected(): void
    {
        $this->expectException(InvalidPasswordHashPolicyException::class);
        PasswordHashPolicy::bcrypt(32);
    }

    public function testNativeAdaptersRemainBehindStableContracts(): void
    {
        self::assertInstanceOf(
            PasswordHasherInterface::class,
            new NativePasswordHasher(PasswordHashPolicy::runtimeDefault())
        );
        self::assertInstanceOf(
            PasswordVerifierInterface::class,
            new NativePasswordVerifier(PasswordHashPolicy::runtimeDefault())
        );
    }
}
