<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\IdentityInterface;
use Sif\Foundation\Security\Contracts\PasswordHasherInterface;
use Sif\Foundation\Security\Contracts\PasswordHashStoreInterface;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Password\Authentication\PasswordHashProviderResult;
use Sif\Foundation\Security\Password\PasswordCredential;
use Sif\Foundation\Security\Password\PasswordHashAlgorithm;
use Sif\Foundation\Security\Password\PasswordSecret;
use Sif\Foundation\Security\Password\Rehash\PasswordRehashCoordinator;
use Sif\Foundation\Security\Password\StoredPasswordHash;

final class PasswordHashStoreAndTransactionalRehashTest extends TestCase
{
    public function testRehashesVerifiedCredentialAndReplacesStoredHash(): void
    {
        $identity = new Identity(new IdentityId('identity-1'));
        $replacement = $this->hash('replacement');
        $store = new InMemoryPasswordHashStoreForTest($this->hash('current'));
        $coordinator = new PasswordRehashCoordinator(
            new FixedPasswordHasherForTest($replacement),
            $store
        );

        $coordinator->rehash(
            $identity,
            new PasswordCredential(new PasswordSecret('verified-secret')),
            $this->hash('current')
        );

        self::assertSame($replacement, $store->findFor($identity)->hash());
        self::assertSame(1, $store->writes());
    }

    public function testStoreContractSupportsReadAndReplaceWithoutPersistenceCoupling(): void
    {
        $identity = new Identity(new IdentityId('identity-2'));
        $store = new InMemoryPasswordHashStoreForTest($this->hash('initial'));
        $next = $this->hash('next');

        self::assertTrue($store->findFor($identity)->wasFound());
        $store->replaceFor($identity, $next);

        self::assertSame($next, $store->findFor($identity)->hash());
    }

    public function testCoordinatorNeverSerializesCredentialOrSecret(): void
    {
        $credential = new PasswordCredential(new PasswordSecret('sensitive-value'));

        $this->expectException(\LogicException::class);
        serialize($credential);
    }

    private function hash(string $value): StoredPasswordHash
    {
        return new StoredPasswordHash(
            new PasswordHashAlgorithm('bcrypt'),
            '$2y$10$' . str_pad($value, 53, 'x')
        );
    }
}

final class FixedPasswordHasherForTest implements PasswordHasherInterface
{
    public function __construct(private readonly StoredPasswordHash $hash)
    {
    }

    public function hash(PasswordSecret $secret): StoredPasswordHash
    {
        return $this->hash;
    }
}

final class InMemoryPasswordHashStoreForTest implements PasswordHashStoreInterface
{
    private int $writes = 0;

    public function __construct(private StoredPasswordHash $hash)
    {
    }

    public function findFor(IdentityInterface $identity): PasswordHashProviderResult
    {
        return PasswordHashProviderResult::found($this->hash);
    }

    public function replaceFor(IdentityInterface $identity, StoredPasswordHash $hash): void
    {
        $this->hash = $hash;
        $this->writes++;
    }

    public function writes(): int
    {
        return $this->writes;
    }
}
