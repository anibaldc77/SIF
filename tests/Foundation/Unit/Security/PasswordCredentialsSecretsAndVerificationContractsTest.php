<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\PasswordVerifierInterface;
use Sif\Foundation\Security\Exceptions\InvalidPasswordHashException;
use Sif\Foundation\Security\Exceptions\InvalidPasswordSecretException;
use Sif\Foundation\Security\Password\PasswordCredential;
use Sif\Foundation\Security\Password\PasswordHashAlgorithm;
use Sif\Foundation\Security\Password\PasswordSecret;
use Sif\Foundation\Security\Password\PasswordVerificationResult;
use Sif\Foundation\Security\Password\StoredPasswordHash;

final class PasswordCredentialsSecretsAndVerificationContractsTest extends TestCase
{
    public function testPasswordSecretIsExplicitlyRedactedAndOnlyExposedToCallback(): void
    {
        $secret = new PasswordSecret('correct horse battery staple');

        self::assertSame(28, $secret->length());
        self::assertSame('[REDACTED]', (string) $secret);
        self::assertSame(28, $secret->expose(static fn (string $value): int => strlen($value)));
        self::assertStringNotContainsString('correct horse', print_r($secret, true));
    }

    public function testPasswordCredentialUsesStableTypeAndCannotBeSerialized(): void
    {
        $credential = new PasswordCredential(new PasswordSecret('secret'));

        self::assertSame('password', $credential->type()->value());

        $this->expectException(\LogicException::class);
        serialize($credential);
    }

    public function testStoredHashSeparatesOpaqueHashFromCanonicalMetadata(): void
    {
        $hash = new StoredPasswordHash(
            new PasswordHashAlgorithm('Argon2.ID'),
            '$opaque$encoded$value',
            ['memory' => 65536, 'parallelism' => 2, 'time' => 4]
        );

        self::assertSame('argon2.id', $hash->algorithm()->value());
        self::assertSame(
            ['memory' => 65536, 'parallelism' => 2, 'time' => 4],
            $hash->parameters()
        );
        self::assertTrue($hash->exposeEncodedHash(static fn (string $value): bool => str_starts_with($value, '$opaque$')));
        self::assertStringNotContainsString('$opaque$encoded$value', print_r($hash, true));
    }

    public function testVerificationResultSeparatesAcceptanceFromRehashAdvice(): void
    {
        $accepted = PasswordVerificationResult::verified(true);
        $rejected = PasswordVerificationResult::rejected();

        self::assertTrue($accepted->isVerified());
        self::assertTrue($accepted->requiresRehash());
        self::assertFalse($rejected->isVerified());
        self::assertFalse($rejected->requiresRehash());
    }

    public function testVerifierContractDoesNotFixAlgorithmOrPersistence(): void
    {
        $reflection = new \ReflectionClass(PasswordVerifierInterface::class);
        $source = file_get_contents((string) $reflection->getFileName());

        self::assertIsString($source);
        self::assertStringNotContainsString('password_verify', $source);
        self::assertStringNotContainsString('BaseModel', $source);
        self::assertStringNotContainsString('PDO', $source);
    }

    public function testInvalidSecretsAndHashMetadataAreRejected(): void
    {
        try {
            new PasswordSecret('');
            self::fail('Empty passwords must be rejected.');
        } catch (InvalidPasswordSecretException) {
            self::assertTrue(true);
        }

        $this->expectException(InvalidPasswordHashException::class);
        new StoredPasswordHash(
            new PasswordHashAlgorithm('argon2id'),
            '$hash',
            ['Invalid Name' => 1]
        );
    }
}
