<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Security\Contracts\FederatedIdentityLinkRevokerInterface;
use Sif\Foundation\Security\Contracts\FederatedProviderCredentialRevokerInterface;
use Sif\Foundation\Security\Contracts\FederatedRevocationJournalInterface;
use Sif\Foundation\Security\Contracts\FederatedSecurityOperationEventPublisherInterface;
use Sif\Foundation\Security\Contracts\FederatedSessionRevokerInterface;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Operations\FederatedSecurityOperationEvent;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationCoordinator;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationExecutionRecord;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationIdempotencyGuard;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationOperationId;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationOrchestrator;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationReason;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationRequest;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationRetryDecision;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationScope;

final class FederatedRevocationJournalingIdempotencyAndSafeRetryTest extends TestCase
{
    public function testUnknownOperationIsExecutable(): void
    {
        $guard = new FederatedRevocationIdempotencyGuard(
            new InMemoryFederatedRevocationJournal()
        );

        self::assertSame(
            FederatedRevocationRetryDecision::Execute,
            $guard->decide($this->operationId())
        );
    }

    public function testCompletedOperationIsReusedWithoutExecutingAgain(): void
    {
        $journal = new InMemoryFederatedRevocationJournal();
        $operations = new JournalingRevocationOperations();

        $coordinator = $this->coordinator(
            $journal,
            $operations
        );

        $first = $coordinator->execute(
            $this->operationId(),
            $this->request(),
            $this->now()
        );

        $second = $coordinator->execute(
            $this->operationId(),
            $this->request(),
            $this->now()->modify('+1 minute')
        );

        self::assertTrue($first->succeeded());
        self::assertSame($first, $second);
        self::assertSame(
            ['local_sessions', 'provider_credentials', 'identity_link'],
            $operations->operations()
        );
        self::assertSame(1, $journal->saveCount());
    }

    public function testIncompleteOperationIsEligibleForRetry(): void
    {
        $journal = new InMemoryFederatedRevocationJournal();
        $operations = new JournalingRevocationOperations(
            failProviderOnce: true
        );

        $coordinator = $this->coordinator(
            $journal,
            $operations
        );

        $first = $coordinator->execute(
            $this->operationId(),
            $this->request(),
            $this->now()
        );

        self::assertFalse($first->succeeded());

        $guard = new FederatedRevocationIdempotencyGuard(
            $journal
        );

        self::assertSame(
            FederatedRevocationRetryDecision::RetryIncomplete,
            $guard->decide($this->operationId())
        );

        $second = $coordinator->execute(
            $this->operationId(),
            $this->request(),
            $this->now()->modify('+1 minute')
        );

        self::assertTrue($second->succeeded());
        self::assertSame(2, $journal->saveCount());
    }

    public function testJournalPersistsLatestExecutionForOperationId(): void
    {
        $journal = new InMemoryFederatedRevocationJournal();
        $operations = new JournalingRevocationOperations();

        $this->coordinator(
            $journal,
            $operations
        )->execute(
            $this->operationId(),
            $this->request(),
            $this->now()
        );

        $record = $journal->find(
            $this->operationId()
        );

        self::assertNotNull($record);
        self::assertTrue($record->completed());
        self::assertSame(
            'revocation-operation-0001',
            $record->operationId()->value()
        );
    }

    public function testJournalContractRemainsPersistenceNeutral(): void
    {
        $reflection = new \ReflectionClass(
            FederatedRevocationJournalInterface::class
        );

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('PDO', $source);
        self::assertStringNotContainsString('SQL', strtoupper($source));
        self::assertStringNotContainsString('Redis', $source);
        self::assertStringNotContainsString('Filesystem', $source);
    }

    public function testCoordinatorDoesNotImplementSchedulerBackoffOrSleeping(): void
    {
        $reflection = new \ReflectionClass(
            FederatedRevocationCoordinator::class
        );

        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('sleep(', strtolower($source));
        self::assertStringNotContainsString('usleep(', strtolower($source));
        self::assertStringNotContainsString('backoff', strtolower($source));
        self::assertStringNotContainsString('cron', strtolower($source));
    }

    private function coordinator(
        InMemoryFederatedRevocationJournal $journal,
        JournalingRevocationOperations $operations
    ): FederatedRevocationCoordinator {
        $orchestrator = new FederatedRevocationOrchestrator(
            new JournalingSessionRevoker($operations),
            new JournalingProviderCredentialRevoker($operations),
            new JournalingIdentityLinkRevoker($operations),
            new NullFederatedSecurityOperationEventPublisher()
        );

        return new FederatedRevocationCoordinator(
            $orchestrator,
            $journal,
            new FederatedRevocationIdempotencyGuard($journal)
        );
    }

    private function operationId(): FederatedRevocationOperationId
    {
        return new FederatedRevocationOperationId(
            'revocation-operation-0001'
        );
    }

    private function request(): FederatedRevocationRequest
    {
        return new FederatedRevocationRequest(
            new IdentityId('local-user-1'),
            new OidcFederatedIdentity(
                'https://identity.example',
                'subject-123'
            ),
            FederatedRevocationScope::All,
            new FederatedRevocationReason(
                'security.incident'
            )
        );
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(
            '2026-08-07T19:00:00+00:00'
        );
    }
}

final class InMemoryFederatedRevocationJournal implements FederatedRevocationJournalInterface
{
    /** @var array<string,FederatedRevocationExecutionRecord> */
    private array $records = [];

    private int $saveCount = 0;

    public function find(
        FederatedRevocationOperationId $operationId
    ): ?FederatedRevocationExecutionRecord {
        return $this->records[$operationId->value()] ?? null;
    }

    public function save(
        FederatedRevocationExecutionRecord $record
    ): void {
        $this->saveCount++;
        $this->records[$record->operationId()->value()] = $record;
    }

    public function saveCount(): int
    {
        return $this->saveCount;
    }
}

final class JournalingRevocationOperations
{
    /** @var list<string> */
    private array $operations = [];

    private bool $providerFailed = false;

    public function __construct(
        private bool $failProviderOnce = false
    ) {
    }

    public function record(string $operation): void
    {
        $this->operations[] = $operation;

        if (
            $operation === 'provider_credentials'
            && $this->failProviderOnce
            && !$this->providerFailed
        ) {
            $this->providerFailed = true;

            throw new RuntimeException(
                'Simulated provider failure.'
            );
        }
    }

    /** @return list<string> */
    public function operations(): array
    {
        return $this->operations;
    }
}

final readonly class JournalingSessionRevoker implements FederatedSessionRevokerInterface
{
    public function __construct(
        private JournalingRevocationOperations $operations
    ) {
    }

    public function revokeAll(
        IdentityId $identityId,
        FederatedRevocationReason $reason
    ): void {
        $this->operations->record('local_sessions');
    }
}

final readonly class JournalingProviderCredentialRevoker implements FederatedProviderCredentialRevokerInterface
{
    public function __construct(
        private JournalingRevocationOperations $operations
    ) {
    }

    public function revoke(
        OidcFederatedIdentity $federatedIdentity,
        FederatedRevocationReason $reason
    ): void {
        $this->operations->record('provider_credentials');
    }
}

final readonly class JournalingIdentityLinkRevoker implements FederatedIdentityLinkRevokerInterface
{
    public function __construct(
        private JournalingRevocationOperations $operations
    ) {
    }

    public function revoke(
        OidcFederatedIdentity $federatedIdentity,
        FederatedRevocationReason $reason
    ): bool {
        $this->operations->record('identity_link');

        return true;
    }
}

final class NullFederatedSecurityOperationEventPublisher implements FederatedSecurityOperationEventPublisherInterface
{
    public function publish(
        FederatedSecurityOperationEvent $event
    ): void {
    }
}
