<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialStatusAllocationRepositoryInterface;
use Sif\Foundation\Security\Contracts\CredentialStatusLifecyclePolicyInterface;
use Sif\Foundation\Security\Contracts\CredentialStatusPublicationPlannerInterface;
use Sif\Foundation\Security\Contracts\CredentialStatusPublisherInterface;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusMechanism;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusPurpose;
use Sif\Foundation\Security\VerifiableCredentials\Status\Issuer\CredentialStatusAllocation;
use Sif\Foundation\Security\VerifiableCredentials\Status\Issuer\CredentialStatusLifecycleState;
use Sif\Foundation\Security\VerifiableCredentials\Status\Issuer\CredentialStatusLifecycleTransition;
use Sif\Foundation\Security\VerifiableCredentials\Status\Issuer\CredentialStatusPublicationPlan;
use Sif\Foundation\Security\VerifiableCredentials\Status\Issuer\CredentialStatusPublicationResult;

final class CredentialStatusIssuerPublicationAllocationAndLifecycleTest extends TestCase
{
    public function testLifecycleStatesAreExplicit(): void
    {
        self::assertSame(
            'valid',
            CredentialStatusLifecycleState::Valid->value
        );
        self::assertSame(
            'suspended',
            CredentialStatusLifecycleState::Suspended->value
        );
        self::assertSame(
            'revoked',
            CredentialStatusLifecycleState::Revoked->value
        );
    }

    public function testAllocationKeepsCredentialListIndexAndPurposeExplicit(): void
    {
        $allocation = new CredentialStatusAllocation(
            'credential-001',
            'status-list-001',
            42,
            CredentialStatusPurpose::Revocation
        );

        self::assertSame('credential-001', $allocation->credentialId());
        self::assertSame('status-list-001', $allocation->statusListId());
        self::assertSame(42, $allocation->index());
        self::assertSame(
            CredentialStatusPurpose::Revocation,
            $allocation->purpose()
        );
    }

    public function testLifecycleTransitionKeepsStateTimeAndReasonExplicit(): void
    {
        $effectiveAt = new DateTimeImmutable(
            '2026-08-11T12:30:00Z'
        );

        $transition = new CredentialStatusLifecycleTransition(
            CredentialStatusLifecycleState::Valid,
            CredentialStatusLifecycleState::Suspended,
            $effectiveAt,
            'temporary investigation'
        );

        self::assertSame(
            CredentialStatusLifecycleState::Valid,
            $transition->from()
        );
        self::assertSame(
            CredentialStatusLifecycleState::Suspended,
            $transition->to()
        );
        self::assertSame($effectiveAt, $transition->effectiveAt());
        self::assertSame(
            'temporary investigation',
            $transition->reason()
        );
    }

    public function testPublicationPlanKeepsMechanismVersionScheduleAndAllocationsExplicit(): void
    {
        $allocation = new CredentialStatusAllocation(
            'credential-001',
            'status-list-001',
            7,
            CredentialStatusPurpose::Suspension
        );
        $publishAt = new DateTimeImmutable(
            '2026-08-11T12:45:00Z'
        );

        $plan = new CredentialStatusPublicationPlan(
            'status-list-001',
            CredentialStatusMechanism::BitstringStatusList,
            'version-42',
            $publishAt,
            [$allocation]
        );

        self::assertSame('status-list-001', $plan->statusListId());
        self::assertSame(
            CredentialStatusMechanism::BitstringStatusList,
            $plan->mechanism()
        );
        self::assertSame('version-42', $plan->version());
        self::assertSame($publishAt, $plan->publishAt());
        self::assertCount(1, $plan->allocations());
    }

    public function testPublicationResultKeepsVersionTimestampAndWarningsExplicit(): void
    {
        $publishedAt = new DateTimeImmutable(
            '2026-08-11T12:46:00Z'
        );
        $result = new CredentialStatusPublicationResult(
            true,
            'status-list-001',
            'version-42',
            $publishedAt,
            ['replica propagation pending']
        );

        self::assertTrue($result->published());
        self::assertSame('status-list-001', $result->statusListId());
        self::assertSame('version-42', $result->version());
        self::assertSame($publishedAt, $result->publishedAt());
        self::assertSame(
            ['replica propagation pending'],
            $result->warnings()
        );
    }

    public function testIssuerContractsAreTypedAndSeparated(): void
    {
        $allocation = new \ReflectionMethod(
            CredentialStatusAllocationRepositoryInterface::class,
            'allocate'
        );
        $planner = new \ReflectionMethod(
            CredentialStatusPublicationPlannerInterface::class,
            'plan'
        );
        $publisher = new \ReflectionMethod(
            CredentialStatusPublisherInterface::class,
            'publish'
        );

        self::assertSame(
            CredentialStatusAllocation::class,
            (string) $allocation->getReturnType()
        );
        self::assertSame(
            CredentialStatusPublicationPlan::class,
            (string) $planner->getReturnType()
        );
        self::assertSame(
            CredentialStatusPublicationResult::class,
            (string) $publisher->getReturnType()
        );
        self::assertTrue(
            (new \ReflectionClass(
                CredentialStatusLifecyclePolicyInterface::class
            ))->isInterface()
        );
    }

    public function testIssuerLifecycleLayerRemainsStorageTransportLockAndSchedulerNeutral(): void
    {
        foreach ([
            CredentialStatusAllocationRepositoryInterface::class,
            CredentialStatusLifecyclePolicyInterface::class,
            CredentialStatusPublicationPlannerInterface::class,
            CredentialStatusPublisherInterface::class,
            CredentialStatusAllocation::class,
            CredentialStatusLifecycleTransition::class,
            CredentialStatusPublicationPlan::class,
            CredentialStatusPublicationResult::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('flock(', strtolower($source));
            self::assertStringNotContainsString('sleep(', strtolower($source));
            self::assertStringNotContainsString('cron', strtolower($source));
        }
    }
}
