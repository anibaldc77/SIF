<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\IdentityInterface;
use Sif\Foundation\Security\Contracts\IdentityProviderInterface;
use Sif\Foundation\Security\Contracts\PasswordHasherInterface;
use Sif\Foundation\Security\Contracts\PasswordHashStoreInterface;
use Sif\Foundation\Security\Contracts\RecoveryChallengeDeliveryInterface;
use Sif\Foundation\Security\Contracts\RecoveryTokenGeneratorInterface;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\IdentityProvider\IdentityAccountStatus;
use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderId;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderRecord;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderResult;
use Sif\Foundation\Security\Password\Authentication\PasswordHashProviderResult;
use Sif\Foundation\Security\Password\PasswordHashAlgorithm;
use Sif\Foundation\Security\Password\PasswordSecret;
use Sif\Foundation\Security\Password\StoredPasswordHash;
use Sif\Foundation\Security\Recovery\InMemoryRecoveryChallengeStore;
use Sif\Foundation\Security\Recovery\PasswordReset\PasswordResetService;
use Sif\Foundation\Security\Recovery\RecoveryChallenge;
use Sif\Foundation\Security\Recovery\RecoveryChallengeId;
use Sif\Foundation\Security\Recovery\RecoveryChallengeState;
use Sif\Foundation\Security\Recovery\RecoveryToken;

final class PasswordResetWorkflowTest extends TestCase
{
    public function testUnknownIdentityReturnsGenericAcceptedResultWithoutDelivery(): void
    {
        $delivery = new PasswordResetCapturingRecoveryDelivery();
        $service = $this->service(IdentityProviderResult::notFound(), $delivery, new InMemoryRecoveryChallengeStore());

        $result = $service->request(new IdentityLookupKey('missing@example.test'), new DateTimeImmutable('2026-08-06T18:00:00Z'));

        self::assertTrue($result->isAccepted());
        self::assertSame(['accepted' => true], $result->snapshot());
        self::assertNull($delivery->challenge);
    }

    public function testActiveIdentityIssuesAndDeliversPasswordResetChallenge(): void
    {
        $delivery = new PasswordResetCapturingRecoveryDelivery();
        $store = new InMemoryRecoveryChallengeStore();
        $service = $this->service($this->activeIdentityResult(), $delivery, $store);

        $service->request(new IdentityLookupKey('user@example.test'), new DateTimeImmutable('2026-08-06T18:00:00Z'));

        self::assertNotNull($delivery->challenge);
        self::assertNotNull($delivery->token);
        self::assertSame('password_reset', $delivery->challenge->purpose()->value);
        self::assertSame(RecoveryChallengeState::Pending, $store->find($delivery->challenge->id())?->state());
    }

    public function testSecondRequestRevokesPreviousOutstandingChallenge(): void
    {
        $delivery = new PasswordResetCapturingRecoveryDelivery();
        $store = new InMemoryRecoveryChallengeStore();
        $service = $this->service($this->activeIdentityResult(), $delivery, $store);
        $service->request(new IdentityLookupKey('user@example.test'), new DateTimeImmutable('2026-08-06T18:00:00Z'));
        $firstId = $delivery->challenge?->id();

        $service->request(new IdentityLookupKey('user@example.test'), new DateTimeImmutable('2026-08-06T18:01:00Z'));

        self::assertNotNull($firstId);
        self::assertSame(RecoveryChallengeState::Revoked, $store->find($firstId)?->state());
    }

    public function testValidTokenResetsPasswordAndCannotBeReused(): void
    {
        $delivery = new PasswordResetCapturingRecoveryDelivery();
        $store = new InMemoryRecoveryChallengeStore();
        $hashStore = new PasswordResetCapturingPasswordHashStore();
        $service = $this->service($this->activeIdentityResult(), $delivery, $store, $hashStore);
        $service->request(new IdentityLookupKey('user@example.test'), new DateTimeImmutable('2026-08-06T18:00:00Z'));
        self::assertNotNull($delivery->challenge);
        self::assertNotNull($delivery->token);

        $first = $service->confirm(
            $delivery->challenge->id(),
            $delivery->token,
            new PasswordSecret('replacement'),
            new DateTimeImmutable('2026-08-06T18:05:00Z')
        );
        $second = $service->confirm(
            $delivery->challenge->id(),
            $delivery->token,
            new PasswordSecret('another'),
            new DateTimeImmutable('2026-08-06T18:06:00Z')
        );

        self::assertTrue($first->isSucceeded());
        self::assertFalse($second->isSucceeded());
        self::assertSame('identity-1', $hashStore->identity?->id()->value());
        self::assertSame(1, $hashStore->writes);
    }

    public function testWrongTokenDoesNotReplacePasswordHash(): void
    {
        $delivery = new PasswordResetCapturingRecoveryDelivery();
        $hashStore = new PasswordResetCapturingPasswordHashStore();
        $service = $this->service(
            $this->activeIdentityResult(),
            $delivery,
            new InMemoryRecoveryChallengeStore(),
            $hashStore
        );
        $service->request(new IdentityLookupKey('user@example.test'), new DateTimeImmutable('2026-08-06T18:00:00Z'));
        self::assertNotNull($delivery->challenge);

        $result = $service->confirm(
            $delivery->challenge->id(),
            new RecoveryToken('invalid-secure-recovery-token-value'),
            new PasswordSecret('replacement'),
            new DateTimeImmutable('2026-08-06T18:05:00Z')
        );

        self::assertFalse($result->isSucceeded());
        self::assertSame(0, $hashStore->writes);
    }

    private function activeIdentityResult(): IdentityProviderResult
    {
        return IdentityProviderResult::found(new IdentityProviderRecord(
            new Identity(new IdentityId('identity-1')),
            IdentityAccountStatus::Active
        ));
    }

    private function service(
        IdentityProviderResult $identityResult,
        PasswordResetCapturingRecoveryDelivery $delivery,
        InMemoryRecoveryChallengeStore $store,
        ?PasswordResetCapturingPasswordHashStore $hashStore = null
    ): PasswordResetService {
        return new PasswordResetService(
            new PasswordResetFixedIdentityProvider($identityResult),
            new PasswordResetFixedRecoveryTokenGenerator(),
            $store,
            $delivery,
            new PasswordResetFixedPasswordHasher(),
            $hashStore ?? new PasswordResetCapturingPasswordHashStore()
        );
    }
}

final class PasswordResetFixedIdentityProvider implements IdentityProviderInterface
{
    public function __construct(private readonly IdentityProviderResult $result)
    {
    }

    public function id(): IdentityProviderId
    {
        return new IdentityProviderId('test');
    }

    public function resolve(IdentityLookupKey $lookupKey): IdentityProviderResult
    {
        return $this->result;
    }
}

final class PasswordResetFixedRecoveryTokenGenerator implements RecoveryTokenGeneratorInterface
{
    public function generate(): RecoveryToken
    {
        return new RecoveryToken('fixed-secure-recovery-token-value');
    }
}

final class PasswordResetCapturingRecoveryDelivery implements RecoveryChallengeDeliveryInterface
{
    public ?RecoveryChallenge $challenge = null;
    public ?RecoveryToken $token = null;

    public function deliver(IdentityInterface $identity, RecoveryChallenge $challenge, RecoveryToken $token): void
    {
        $this->challenge = $challenge;
        $this->token = $token;
    }
}

final class PasswordResetFixedPasswordHasher implements PasswordHasherInterface
{
    public function hash(PasswordSecret $secret): StoredPasswordHash
    {
        return new StoredPasswordHash(
            new PasswordHashAlgorithm('bcrypt'),
            '$2y$10$' . str_repeat('x', 53)
        );
    }
}

final class PasswordResetCapturingPasswordHashStore implements PasswordHashStoreInterface
{
    public int $writes = 0;
    public ?IdentityInterface $identity = null;

    public function findFor(IdentityInterface $identity): PasswordHashProviderResult
    {
        return PasswordHashProviderResult::notFound();
    }

    public function replaceFor(IdentityInterface $identity, StoredPasswordHash $hash): void
    {
        $this->identity = $identity;
        $this->writes++;
    }
}
