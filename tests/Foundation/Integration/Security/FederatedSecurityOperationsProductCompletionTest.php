<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Integration\Security;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Cli\Command\Security\FederatedRevocationExecuteCommand;
use Sif\Foundation\Cli\Command\Security\FederatedRevocationInspectCommand;
use Sif\Foundation\Security\Contracts\FederatedIdentityLinkRevokerInterface;
use Sif\Foundation\Security\Contracts\FederatedProviderCredentialRevokerInterface;
use Sif\Foundation\Security\Contracts\FederatedProviderRevocationAdapterInterface;
use Sif\Foundation\Security\Contracts\FederatedProviderRevocationCapabilityProviderInterface;
use Sif\Foundation\Security\Contracts\FederatedRevocationJournalInterface;
use Sif\Foundation\Security\Contracts\FederatedSecurityOperationEventPublisherInterface;
use Sif\Foundation\Security\Contracts\FederatedSessionRevokerInterface;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Operations\FederatedSecurityOperationEvent;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationCapabilities;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationCapability;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationCoordinator;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationOutcome;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationPolicy;
use Sif\Foundation\Security\Operations\Provider\FederatedProviderRevocationService;
use Sif\Foundation\Security\Operations\Provider\FederatedRemoteFailure;
use Sif\Foundation\Security\Operations\Provider\FederatedRemoteFailureKind;
use Sif\Foundation\Security\Operations\Revocation\FederatedRemoteRetryBridge;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationCoordinator;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationExecutionRecord;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationIdempotencyGuard;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationOperationId;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationOrchestrator;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationReason;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationRequest;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationResumePlanner;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationRetryAdvisor;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationRetryPolicy;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationScope;

final class FederatedSecurityOperationsProductCompletionTest extends TestCase
{
    public function testEndToEndAdministrativeRevocationCompletesAndCanBeInspected(): void
    {
        $journal = new ProductRevocationJournal();
        $operations = new ProductRevocationOperations();

        $coordinator = $this->coordinator($journal, $operations);

        $operationId = new FederatedRevocationOperationId(
            'revocation-product-operation-0001'
        );

        $execute = new FederatedRevocationExecuteCommand($coordinator);
        $result = $execute->execute(
            $operationId,
            $this->request(),
            $this->now(),
            true
        );

        self::assertTrue($result['executed']);
        self::assertTrue($result['completed']);

        $inspection = (new FederatedRevocationInspectCommand($journal))
            ->execute($operationId);

        self::assertTrue($inspection['found']);
        self::assertTrue($inspection['completed']);
        self::assertSame(
            ['local_sessions', 'provider_credentials', 'identity_link'],
            $operations->operations()
        );
    }

    public function testCompletedOperationIsIdempotentAcrossAdministrativeRetries(): void
    {
        $journal = new ProductRevocationJournal();
        $operations = new ProductRevocationOperations();
        $coordinator = $this->coordinator($journal, $operations);
        $operationId = new FederatedRevocationOperationId(
            'revocation-product-operation-0001'
        );

        $coordinator->execute(
            $operationId,
            $this->request(),
            $this->now()
        );

        $coordinator->execute(
            $operationId,
            $this->request(),
            $this->now()->modify('+1 minute')
        );

        self::assertSame(
            ['local_sessions', 'provider_credentials', 'identity_link'],
            $operations->operations()
        );
        self::assertSame(1, $journal->saveCount());
    }

    public function testResumePlannerExcludesSuccessfulStepsAfterIncompleteExecution(): void
    {
        $journal = new ProductRevocationJournal();
        $operations = new ProductRevocationOperations(
            failProviderOnce: true
        );
        $coordinator = $this->coordinator($journal, $operations);
        $operationId = new FederatedRevocationOperationId(
            'revocation-product-operation-0002'
        );

        $first = $coordinator->execute(
            $operationId,
            $this->request(),
            $this->now()
        );

        self::assertFalse($first->succeeded());

        $plan = (new FederatedRevocationResumePlanner())->plan(
            $this->request(),
            $first
        );

        self::assertSame(
            [
                \Sif\Foundation\Security\Operations\Revocation\FederatedRevocationStep::ProviderCredentials,
                \Sif\Foundation\Security\Operations\Revocation\FederatedRevocationStep::IdentityLink,
            ],
            $plan->remainingSteps()
        );
    }

    public function testTransientRemoteFailureUsesRetryPolicy(): void
    {
        $remote = new FederatedProviderRevocationCoordinator(
            new FederatedProviderRevocationService(
                new ProductCapabilityProvider(),
                new ProductOutcomeAdapter(
                    FederatedProviderRevocationOutcome::failure(
                        new FederatedRemoteFailure(
                            FederatedRemoteFailureKind::Transient,
                            'provider.timeout'
                        )
                    )
                )
            ),
            new FederatedProviderRevocationPolicy()
        );

        $assessment = $remote->execute(
            $this->request()->federatedIdentity(),
            FederatedProviderRevocationCapability::EndSession,
            $this->request()->reason()
        );

        $retry = (new FederatedRemoteRetryBridge(
            new FederatedRevocationRetryAdvisor(
                new FederatedRevocationRetryPolicy(
                    3,
                    new DateInterval('PT30S')
                )
            )
        ))->assess(
            $assessment,
            1,
            $this->now()
        );

        self::assertTrue($retry->allowed());
        self::assertNotNull(
            $retry->state()->nextEligibleAt()
        );
    }

    public function testPermanentRemoteFailureIsTerminal(): void
    {
        $remote = new FederatedProviderRevocationCoordinator(
            new FederatedProviderRevocationService(
                new ProductCapabilityProvider(),
                new ProductOutcomeAdapter(
                    FederatedProviderRevocationOutcome::failure(
                        new FederatedRemoteFailure(
                            FederatedRemoteFailureKind::Permanent,
                            'provider.invalid_client'
                        )
                    )
                )
            ),
            new FederatedProviderRevocationPolicy()
        );

        $assessment = $remote->execute(
            $this->request()->federatedIdentity(),
            FederatedProviderRevocationCapability::EndSession,
            $this->request()->reason()
        );

        self::assertTrue($assessment->terminal());
        self::assertFalse($assessment->retryable());
    }

    public function testProductRemainsProviderStorageAndSchedulerNeutral(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Security/Operations';

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        ) as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            self::assertIsString($source);
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('sleep(', strtolower($source));
            self::assertStringNotContainsString('usleep(', strtolower($source));
        }
    }

    private function coordinator(
        ProductRevocationJournal $journal,
        ProductRevocationOperations $operations
    ): FederatedRevocationCoordinator {
        return new FederatedRevocationCoordinator(
            new FederatedRevocationOrchestrator(
                new ProductSessionRevoker($operations),
                new ProductProviderCredentialRevoker($operations),
                new ProductIdentityLinkRevoker($operations),
                new ProductNullEventPublisher()
            ),
            $journal,
            new FederatedRevocationIdempotencyGuard($journal)
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
            '2026-08-07T21:30:00+00:00'
        );
    }
}

final class ProductRevocationJournal implements FederatedRevocationJournalInterface
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

final class ProductRevocationOperations
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

final readonly class ProductSessionRevoker implements FederatedSessionRevokerInterface
{
    public function __construct(
        private ProductRevocationOperations $operations
    ) {
    }

    public function revokeAll(
        IdentityId $identityId,
        FederatedRevocationReason $reason
    ): void {
        $this->operations->record('local_sessions');
    }
}

final readonly class ProductProviderCredentialRevoker implements FederatedProviderCredentialRevokerInterface
{
    public function __construct(
        private ProductRevocationOperations $operations
    ) {
    }

    public function revoke(
        OidcFederatedIdentity $federatedIdentity,
        FederatedRevocationReason $reason
    ): void {
        $this->operations->record('provider_credentials');
    }
}

final readonly class ProductIdentityLinkRevoker implements FederatedIdentityLinkRevokerInterface
{
    public function __construct(
        private ProductRevocationOperations $operations
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

final class ProductNullEventPublisher implements FederatedSecurityOperationEventPublisherInterface
{
    public function publish(
        FederatedSecurityOperationEvent $event
    ): void {
    }
}

final readonly class ProductCapabilityProvider implements FederatedProviderRevocationCapabilityProviderInterface
{
    public function capabilitiesFor(
        OidcFederatedIdentity $federatedIdentity
    ): FederatedProviderRevocationCapabilities {
        return new FederatedProviderRevocationCapabilities([
            FederatedProviderRevocationCapability::RevokeAccessToken,
            FederatedProviderRevocationCapability::RevokeRefreshToken,
            FederatedProviderRevocationCapability::EndSession,
            FederatedProviderRevocationCapability::GlobalLogout,
        ]);
    }
}

final readonly class ProductOutcomeAdapter implements FederatedProviderRevocationAdapterInterface
{
    public function __construct(
        private FederatedProviderRevocationOutcome $outcome
    ) {
    }

    public function revoke(
        OidcFederatedIdentity $federatedIdentity,
        FederatedProviderRevocationCapability $capability,
        FederatedRevocationReason $reason
    ): FederatedProviderRevocationOutcome {
        return $this->outcome;
    }
}
