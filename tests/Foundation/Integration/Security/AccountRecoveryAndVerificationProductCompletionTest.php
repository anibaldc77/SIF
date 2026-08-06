<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Integration\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\IdentityInterface;
use Sif\Foundation\Security\Contracts\IdentityProviderInterface;
use Sif\Foundation\Security\Contracts\IdentityVerificationActivatorInterface;
use Sif\Foundation\Security\Contracts\PasswordHasherInterface;
use Sif\Foundation\Security\Contracts\PasswordHashStoreInterface;
use Sif\Foundation\Security\Contracts\RecoveryChallengeDeliveryInterface;
use Sif\Foundation\Security\Contracts\RecoverySecurityEventHandlerInterface;
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
use Sif\Foundation\Security\Recovery\Events\RecoverySecurityEvent;
use Sif\Foundation\Security\Recovery\IdentityVerification\IdentityVerificationService;
use Sif\Foundation\Security\Recovery\InMemoryRecoveryChallengeStore;
use Sif\Foundation\Security\Recovery\PasswordReset\PasswordResetService;
use Sif\Foundation\Security\Recovery\RecoveryChallenge;
use Sif\Foundation\Security\Recovery\RecoveryChallengePurpose;
use Sif\Foundation\Security\Recovery\RecoveryToken;

final class AccountRecoveryAndVerificationProductCompletionTest extends TestCase
{
    public function testPasswordResetIsSingleUseAndReplacesHashOnce(): void
    {
        $delivery = new CompletionCapturingDelivery();
        $hashStore = new CompletionHashStore();
        $events = new CompletionEventCollector();
        $service = new PasswordResetService(
            new CompletionIdentityProvider(true),
            new CompletionTokenGenerator(),
            new InMemoryRecoveryChallengeStore(),
            $delivery,
            new CompletionHasher(),
            $hashStore,
            events: $events
        );
        $now = new DateTimeImmutable('2026-08-06T18:00:00Z');
        $service->request(new IdentityLookupKey('user@example.test'), $now);
        self::assertNotNull($delivery->challenge);
        self::assertNotNull($delivery->token);

        $first = $service->confirm($delivery->challenge->id(), $delivery->token, new PasswordSecret('replacement'), $now->modify('+1 minute'));
        $second = $service->confirm($delivery->challenge->id(), $delivery->token, new PasswordSecret('replacement-2'), $now->modify('+2 minutes'));

        self::assertTrue($first->isSucceeded());
        self::assertFalse($second->isSucceeded());
        self::assertSame(1, $hashStore->writes);
        self::assertSame('identity-1', $hashStore->identityId);
        self::assertGreaterThanOrEqual(3, count($events->events));
    }

    public function testVerificationUsesIndependentPurposeAndCannotReplay(): void
    {
        $delivery = new CompletionCapturingDelivery();
        $activator = new CompletionActivator();
        $service = new IdentityVerificationService(
            new CompletionIdentityProvider(true),
            new CompletionTokenGenerator(),
            new InMemoryRecoveryChallengeStore(),
            $delivery,
            $activator
        );
        $now = new DateTimeImmutable('2026-08-06T18:00:00Z');
        $service->request(new IdentityLookupKey('user@example.test'), $now);
        self::assertNotNull($delivery->challenge);
        self::assertSame(RecoveryChallengePurpose::IdentityVerification, $delivery->challenge->purpose());
        self::assertNotNull($delivery->token);

        self::assertTrue($service->confirm($delivery->challenge->id(), $delivery->token, $now->modify('+1 minute'))->isSucceeded());
        self::assertFalse($service->confirm($delivery->challenge->id(), $delivery->token, $now->modify('+2 minutes'))->isSucceeded());
        self::assertSame('identity-1', $activator->identityId);
    }

    public function testUnknownIdentityIsGenericAndSnapshotsExposeNoSecrets(): void
    {
        $delivery = new CompletionCapturingDelivery();
        $events = new CompletionEventCollector();
        $service = new PasswordResetService(
            new CompletionIdentityProvider(false),
            new CompletionTokenGenerator(),
            new InMemoryRecoveryChallengeStore(),
            $delivery,
            new CompletionHasher(),
            new CompletionHashStore(),
            events: $events
        );
        $result = $service->request(new IdentityLookupKey('missing@example.test'), new DateTimeImmutable('2026-08-06T18:00:00Z'));

        self::assertTrue($result->isAccepted());
        self::assertNull($delivery->challenge);
        $json = json_encode(array_map(static fn (RecoverySecurityEvent $event): array => $event->snapshot(), $events->events), JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('missing@example.test', $json);
        self::assertStringNotContainsString('fixed-secure-recovery-token-value', $json);
        self::assertStringNotContainsString('digest', $json);
    }
}

final class CompletionIdentityProvider implements IdentityProviderInterface
{
    public function __construct(private readonly bool $found) {}
    public function id(): IdentityProviderId { return new IdentityProviderId('completion'); }
    public function resolve(IdentityLookupKey $lookupKey): IdentityProviderResult
    {
        return $this->found
            ? IdentityProviderResult::found(new IdentityProviderRecord(new Identity(new IdentityId('identity-1')), IdentityAccountStatus::Active))
            : IdentityProviderResult::notFound();
    }
}

final class CompletionTokenGenerator implements RecoveryTokenGeneratorInterface
{
    public function generate(): RecoveryToken { return new RecoveryToken('fixed-secure-recovery-token-value'); }
}

final class CompletionCapturingDelivery implements RecoveryChallengeDeliveryInterface
{
    public ?RecoveryChallenge $challenge = null;
    public ?RecoveryToken $token = null;
    public function deliver(IdentityInterface $identity, RecoveryChallenge $challenge, RecoveryToken $token): void
    { $this->challenge = $challenge; $this->token = $token; }
}

final class CompletionHasher implements PasswordHasherInterface
{
    public function hash(PasswordSecret $secret): StoredPasswordHash
    { return new StoredPasswordHash(new PasswordHashAlgorithm('bcrypt'), '$2y$10$' . str_repeat('x', 53)); }
}

final class CompletionHashStore implements PasswordHashStoreInterface
{
    public int $writes = 0;
    public ?string $identityId = null;
    public function findFor(IdentityInterface $identity): PasswordHashProviderResult { return PasswordHashProviderResult::notFound(); }
    public function replaceFor(IdentityInterface $identity, StoredPasswordHash $hash): void
    { $this->writes++; $this->identityId = $identity->id()->value(); }
}

final class CompletionActivator implements IdentityVerificationActivatorInterface
{
    public ?string $identityId = null;
    public function markVerified(IdentityInterface $identity): void { $this->identityId = $identity->id()->value(); }
}

final class CompletionEventCollector implements RecoverySecurityEventHandlerInterface
{
    /** @var list<RecoverySecurityEvent> */
    public array $events = [];
    public function handle(RecoverySecurityEvent $event): void { $this->events[] = $event; }
}
