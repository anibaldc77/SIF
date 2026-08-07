<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Cli\Command\Security\FederatedRevocationExecuteCommand;
use Sif\Foundation\Cli\Command\Security\FederatedRevocationInspectCommand;
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
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationScope;

final class FederatedAdministrativeCliSecurityOperationsTest extends TestCase
{
    public function testInspectionReturnsNotFoundWithoutSideEffects(): void
    {
        $journal = new CliRevocationJournal();

        $result = (new FederatedRevocationInspectCommand($journal))->execute(
            new FederatedRevocationOperationId('revocation-operation-cli-0001')
        );

        self::assertFalse($result['found']);
        self::assertSame(0, $journal->saveCount());
    }

    public function testExecutionRequiresExplicitConfirmation(): void
    {
        $ops = new CliRevocationOperations();
        $journal = new CliRevocationJournal();

        $result = (new FederatedRevocationExecuteCommand(
            $this->coordinator($journal, $ops)
        ))->execute(
            new FederatedRevocationOperationId('revocation-operation-cli-0001'),
            $this->request(),
            $this->now(),
            false
        );

        self::assertFalse($result['executed']);
        self::assertSame('confirmation_required', $result['reason']);
        self::assertSame([], $ops->operations());
    }

    public function testConfirmedExecutionDelegatesToCoordinator(): void
    {
        $ops = new CliRevocationOperations();
        $journal = new CliRevocationJournal();

        $result = (new FederatedRevocationExecuteCommand(
            $this->coordinator($journal, $ops)
        ))->execute(
            new FederatedRevocationOperationId('revocation-operation-cli-0001'),
            $this->request(),
            $this->now(),
            true
        );

        self::assertTrue($result['executed']);
        self::assertTrue($result['completed']);
        self::assertSame(
            ['local_sessions', 'provider_credentials', 'identity_link'],
            $ops->operations()
        );
    }

    public function testInspectionReportsExecutionWithoutSecrets(): void
    {
        $ops = new CliRevocationOperations();
        $journal = new CliRevocationJournal();
        $operationId = new FederatedRevocationOperationId(
            'revocation-operation-cli-0001'
        );

        (new FederatedRevocationExecuteCommand(
            $this->coordinator($journal, $ops)
        ))->execute(
            $operationId,
            $this->request(),
            $this->now(),
            true
        );

        $result = (new FederatedRevocationInspectCommand($journal))
            ->execute($operationId);

        self::assertTrue($result['found']);
        self::assertTrue($result['completed']);

        $serialized = json_encode($result);
        self::assertIsString($serialized);

        foreach (['access_token', 'refresh_token', 'id_token', 'secret'] as $forbidden) {
            self::assertStringNotContainsString(
                $forbidden,
                strtolower($serialized)
            );
        }
    }

    public function testCliCommandsDoNotParseArgvOrExitDirectly(): void
    {
        foreach ([
            FederatedRevocationInspectCommand::class,
            FederatedRevocationExecuteCommand::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('$argv', $source);
            self::assertStringNotContainsString('exit(', strtolower($source));
            self::assertStringNotContainsString('die(', strtolower($source));
            self::assertStringNotContainsString('readline(', strtolower($source));
        }
    }

    public function testCliLayerDoesNotPerformProviderTransportDirectly(): void
    {
        $directory = dirname(__DIR__, 4)
            . '/src/Foundation/Cli/Command/Security';

        foreach (glob($directory . '/FederatedRevocation*.php') ?: [] as $file) {
            $source = file_get_contents($file);

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('http://', strtolower($source));
            self::assertStringNotContainsString('https://', strtolower($source));
        }
    }

    private function coordinator(
        CliRevocationJournal $journal,
        CliRevocationOperations $operations
    ): FederatedRevocationCoordinator {
        return new FederatedRevocationCoordinator(
            new FederatedRevocationOrchestrator(
                new CliSessionRevoker($operations),
                new CliProviderRevoker($operations),
                new CliIdentityLinkRevoker($operations),
                new CliNullEventPublisher()
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
            new FederatedRevocationReason('administrator.action')
        );
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-07T21:00:00+00:00');
    }
}

final class CliRevocationJournal implements FederatedRevocationJournalInterface
{
    /** @var array<string,FederatedRevocationExecutionRecord> */
    private array $records = [];
    private int $saveCount = 0;

    public function find(
        FederatedRevocationOperationId $operationId
    ): ?FederatedRevocationExecutionRecord {
        return $this->records[$operationId->value()] ?? null;
    }

    public function save(FederatedRevocationExecutionRecord $record): void
    {
        $this->saveCount++;
        $this->records[$record->operationId()->value()] = $record;
    }

    public function saveCount(): int
    {
        return $this->saveCount;
    }
}

final class CliRevocationOperations
{
    /** @var list<string> */
    private array $operations = [];

    public function record(string $operation): void
    {
        $this->operations[] = $operation;
    }

    /** @return list<string> */
    public function operations(): array
    {
        return $this->operations;
    }
}

final readonly class CliSessionRevoker implements FederatedSessionRevokerInterface
{
    public function __construct(private CliRevocationOperations $operations)
    {
    }

    public function revokeAll(
        IdentityId $identityId,
        FederatedRevocationReason $reason
    ): void {
        $this->operations->record('local_sessions');
    }
}

final readonly class CliProviderRevoker implements FederatedProviderCredentialRevokerInterface
{
    public function __construct(private CliRevocationOperations $operations)
    {
    }

    public function revoke(
        OidcFederatedIdentity $federatedIdentity,
        FederatedRevocationReason $reason
    ): void {
        $this->operations->record('provider_credentials');
    }
}

final readonly class CliIdentityLinkRevoker implements FederatedIdentityLinkRevokerInterface
{
    public function __construct(private CliRevocationOperations $operations)
    {
    }

    public function revoke(
        OidcFederatedIdentity $federatedIdentity,
        FederatedRevocationReason $reason
    ): bool {
        $this->operations->record('identity_link');

        return true;
    }
}

final class CliNullEventPublisher implements FederatedSecurityOperationEventPublisherInterface
{
    public function publish(FederatedSecurityOperationEvent $event): void
    {
    }
}
