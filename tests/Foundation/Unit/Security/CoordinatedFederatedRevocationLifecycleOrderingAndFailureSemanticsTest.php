<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Security\Contracts\FederatedIdentityLinkRevokerInterface;
use Sif\Foundation\Security\Contracts\FederatedProviderCredentialRevokerInterface;
use Sif\Foundation\Security\Contracts\FederatedSecurityOperationEventPublisherInterface;
use Sif\Foundation\Security\Contracts\FederatedSessionRevokerInterface;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Oidc\OidcFederatedIdentity;
use Sif\Foundation\Security\Operations\FederatedSecurityOperationEvent;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationOrchestrator;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationReason;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationRequest;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationScope;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationStep;

final class CoordinatedFederatedRevocationLifecycleOrderingAndFailureSemanticsTest extends TestCase
{
    public function testAllScopeExecutesLocalProviderAndLinkInDeterministicOrder(): void
    {
        $operations = new RevocationOperationSequence();

        $execution = $this->orchestrator(
            $operations,
            new RevocationEventRecorder()
        )->execute(
            $this->request(FederatedRevocationScope::All),
            $this->now()
        );

        self::assertSame(
            ['local_sessions', 'provider_credentials', 'identity_link'],
            $operations->operations()
        );
        self::assertTrue($execution->succeeded());
        self::assertTrue($execution->result()->localSessionsRevoked());
        self::assertTrue($execution->result()->providerCredentialsRevoked());
        self::assertTrue($execution->result()->identityLinkRevoked());
    }

    public function testSingleScopeDoesNotExecuteUnrequestedOperations(): void
    {
        $operations = new RevocationOperationSequence();

        $execution = $this->orchestrator(
            $operations,
            new RevocationEventRecorder()
        )->execute(
            $this->request(
                FederatedRevocationScope::ProviderCredentials
            ),
            $this->now()
        );

        self::assertSame(
            ['provider_credentials'],
            $operations->operations()
        );
        self::assertTrue($execution->succeeded());
        self::assertFalse(
            $execution->result()->localSessionsRevoked()
        );
        self::assertTrue(
            $execution->result()->providerCredentialsRevoked()
        );
        self::assertFalse(
            $execution->result()->identityLinkRevoked()
        );
    }

    public function testFailureStopsSubsequentDestructiveSteps(): void
    {
        $operations = new RevocationOperationSequence(
            failOn: 'provider_credentials'
        );

        $execution = $this->orchestrator(
            $operations,
            new RevocationEventRecorder()
        )->execute(
            $this->request(FederatedRevocationScope::All),
            $this->now()
        );

        self::assertSame(
            ['local_sessions', 'provider_credentials'],
            $operations->operations()
        );
        self::assertFalse($execution->succeeded());
        self::assertCount(2, $execution->steps());
        self::assertSame(
            FederatedRevocationStep::ProviderCredentials,
            $execution->steps()[1]->step()
        );
        self::assertSame(
            RuntimeException::class,
            $execution->steps()[1]->failureType()
        );
    }

    public function testRejectedIdentityLinkRevocationIsReportedAsIncomplete(): void
    {
        $operations = new RevocationOperationSequence(
            rejectIdentityLink: true
        );

        $execution = $this->orchestrator(
            $operations,
            new RevocationEventRecorder()
        )->execute(
            $this->request(
                FederatedRevocationScope::IdentityLink
            ),
            $this->now()
        );

        self::assertFalse($execution->succeeded());
        self::assertSame(
            'operation_rejected',
            $execution->steps()[0]->failureType()
        );
        self::assertFalse(
            $execution->result()->identityLinkRevoked()
        );
    }

    public function testLifecyclePublishesStartedStepAndTerminalEventsWithoutSecrets(): void
    {
        $events = new RevocationEventRecorder();

        $this->orchestrator(
            new RevocationOperationSequence(),
            $events
        )->execute(
            $this->request(FederatedRevocationScope::All),
            $this->now()
        );

        self::assertSame(
            [
                'federation.revocation.started',
                'federation.revocation.step_succeeded',
                'federation.revocation.step_succeeded',
                'federation.revocation.step_succeeded',
                'federation.revocation.completed',
            ],
            $events->names()
        );

        foreach ($events->events() as $event) {
            $serialized = json_encode($event->context());

            self::assertIsString($serialized);
            self::assertStringNotContainsString(
                'token',
                strtolower($serialized)
            );
            self::assertStringNotContainsString(
                'secret',
                strtolower($serialized)
            );
        }
    }

    public function testOrchestratorDoesNotImplementRetriesCompensationOrProviderSpecificLogic(): void
    {
        $reflection = new \ReflectionClass(
            FederatedRevocationOrchestrator::class
        );
        $source = file_get_contents(
            (string) $reflection->getFileName()
        );

        self::assertIsString($source);
        self::assertStringNotContainsString('sleep(', strtolower($source));
        self::assertStringNotContainsString('retry', strtolower($source));
        self::assertStringNotContainsString('rollback', strtolower($source));
        self::assertStringNotContainsString('Keycloak', $source);
        self::assertStringNotContainsString('curl_', strtolower($source));
    }

    private function orchestrator(
        RevocationOperationSequence $operations,
        RevocationEventRecorder $events
    ): FederatedRevocationOrchestrator {
        return new FederatedRevocationOrchestrator(
            new RevocationSessionRecorder($operations),
            new RevocationProviderCredentialRecorder($operations),
            new RevocationIdentityLinkRecorder($operations),
            $events
        );
    }

    private function request(
        FederatedRevocationScope $scope
    ): FederatedRevocationRequest {
        return new FederatedRevocationRequest(
            new IdentityId('local-user-1'),
            new OidcFederatedIdentity(
                'https://identity.example',
                'subject-123'
            ),
            $scope,
            new FederatedRevocationReason(
                'security.incident'
            )
        );
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(
            '2026-08-07T18:30:00+00:00'
        );
    }
}

final class RevocationOperationSequence
{
    /** @var list<string> */
    private array $operations = [];

    public function __construct(
        private ?string $failOn = null,
        private bool $rejectIdentityLink = false
    ) {
    }

    public function record(string $operation): void
    {
        $this->operations[] = $operation;

        if ($this->failOn === $operation) {
            throw new RuntimeException(
                'Simulated revocation failure.'
            );
        }
    }

    public function shouldRejectIdentityLink(): bool
    {
        return $this->rejectIdentityLink;
    }

    /** @return list<string> */
    public function operations(): array
    {
        return $this->operations;
    }
}

final readonly class RevocationSessionRecorder implements FederatedSessionRevokerInterface
{
    public function __construct(
        private RevocationOperationSequence $operations
    ) {
    }

    public function revokeAll(
        IdentityId $identityId,
        FederatedRevocationReason $reason
    ): void {
        $this->operations->record('local_sessions');
    }
}

final readonly class RevocationProviderCredentialRecorder implements FederatedProviderCredentialRevokerInterface
{
    public function __construct(
        private RevocationOperationSequence $operations
    ) {
    }

    public function revoke(
        OidcFederatedIdentity $federatedIdentity,
        FederatedRevocationReason $reason
    ): void {
        $this->operations->record('provider_credentials');
    }
}

final readonly class RevocationIdentityLinkRecorder implements FederatedIdentityLinkRevokerInterface
{
    public function __construct(
        private RevocationOperationSequence $operations
    ) {
    }

    public function revoke(
        OidcFederatedIdentity $federatedIdentity,
        FederatedRevocationReason $reason
    ): bool {
        $this->operations->record('identity_link');

        return !$this->operations->shouldRejectIdentityLink();
    }
}

final class RevocationEventRecorder implements FederatedSecurityOperationEventPublisherInterface
{
    /** @var list<FederatedSecurityOperationEvent> */
    private array $events = [];

    public function publish(
        FederatedSecurityOperationEvent $event
    ): void {
        $this->events[] = $event;
    }

    /** @return list<string> */
    public function names(): array
    {
        return array_map(
            static fn (FederatedSecurityOperationEvent $event): string => $event->name(),
            $this->events
        );
    }

    /** @return list<FederatedSecurityOperationEvent> */
    public function events(): array
    {
        return $this->events;
    }
}
