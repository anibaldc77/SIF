<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Migration;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Migration\Authorization\MigrationExecutionAuthorization;
use Sif\Foundation\Migration\Authorization\MigrationExecutionAuthorizer;
use Sif\Foundation\Migration\Exceptions\InvalidMigrationSelectionException;
use Sif\Foundation\Migration\Exceptions\IrreversibleMigrationException;
use Sif\Foundation\Migration\Exceptions\MigrationAuthorizationMismatchException;
use Sif\Foundation\Migration\History\MigrationHistory;
use Sif\Foundation\Migration\History\MigrationHistoryRecord;
use Sif\Foundation\Migration\History\MigrationHistoryStatus;
use Sif\Foundation\Migration\MigrationChecksum;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\MigrationExecutionMode;
use Sif\Foundation\Migration\MigrationId;
use Sif\Foundation\Migration\MigrationRequest;
use Sif\Foundation\Migration\MigrationVersion;
use Sif\Foundation\Migration\Registry\MigrationRegistry;
use Sif\Foundation\Migration\Selection\MigrationDryRunReport;
use Sif\Foundation\Migration\Selection\MigrationSelector;

final class MigrationSelectionAuthorizationTest extends TestCase
{
    public function testForwardSelectionIncludesOnlyPendingMigrationsInDependencyOrder(): void
    {
        $registry = $this->registry();
        $history = new MigrationHistory([$this->record('foundation.prepare')]);

        $plan = (new MigrationSelector())->select(
            $registry,
            $history,
            new MigrationRequest(MigrationDirection::up(), MigrationExecutionMode::dryRun()),
        );

        self::assertSame(['users.create', 'audit.create'], $plan->identifiers());
        self::assertSame(2, $plan->count());
    }

    public function testRollbackSelectionUsesReverseOrderAndAppliedHistory(): void
    {
        $registry = $this->registry();
        $history = new MigrationHistory([
            $this->record('foundation.prepare'),
            $this->record('users.create'),
            $this->record('audit.create'),
        ]);

        $plan = (new MigrationSelector())->select(
            $registry,
            $history,
            new MigrationRequest(MigrationDirection::down(), MigrationExecutionMode::dryRun(), limit: 2),
        );

        self::assertSame(['audit.create', 'users.create'], $plan->identifiers());
    }

    public function testTagsAndTargetDoNotReorderSelectedMigrations(): void
    {
        $registry = $this->registry();
        $request = new MigrationRequest(
            MigrationDirection::up(),
            MigrationExecutionMode::dryRun(),
            new MigrationId('audit.create'),
            tags: ['core'],
        );

        $plan = (new MigrationSelector())->select($registry, new MigrationHistory(), $request);

        self::assertSame(['foundation.prepare', 'users.create', 'audit.create'], $plan->identifiers());
    }

    public function testUnknownTargetIsRejected(): void
    {
        $this->expectException(InvalidMigrationSelectionException::class);

        (new MigrationSelector())->select(
            $this->registry(),
            new MigrationHistory(),
            new MigrationRequest(
                MigrationDirection::up(),
                MigrationExecutionMode::dryRun(),
                new MigrationId('missing.target'),
            ),
        );
    }

    public function testIrreversibleRollbackIsRejected(): void
    {
        $descriptor = $this->descriptor('unsafe.change', [], false);
        $history = new MigrationHistory([$this->record('unsafe.change')]);

        $this->expectException(IrreversibleMigrationException::class);
        (new MigrationSelector())->select(
            new MigrationRegistry([$descriptor]),
            $history,
            new MigrationRequest(MigrationDirection::down(), MigrationExecutionMode::apply()),
        );
    }

    public function testDryRunSummaryContainsNoChecksums(): void
    {
        $plan = (new MigrationSelector())->select(
            $this->registry(),
            new MigrationHistory(),
            new MigrationRequest(MigrationDirection::up(), MigrationExecutionMode::dryRun(), limit: 1),
        );
        $summary = (new MigrationDryRunReport($plan))->summary();

        self::assertSame('dry-run', $summary['mode']);
        self::assertSame(1, $summary['count']);
        self::assertSame(['foundation.prepare'], $summary['migrations']);
        self::assertArrayNotHasKey('checksum', $summary);
    }

    public function testAuthorizationMustMatchPlanFingerprintDirectionAndMode(): void
    {
        $plan = (new MigrationSelector())->select(
            $this->registry(),
            new MigrationHistory(),
            new MigrationRequest(MigrationDirection::up(), MigrationExecutionMode::apply()),
        );
        $authorization = new MigrationExecutionAuthorization(
            'approval-20260730',
            $plan->fingerprint(),
            MigrationDirection::up(),
            MigrationExecutionMode::apply(),
            true,
        );

        (new MigrationExecutionAuthorizer())->assertAuthorized($plan, $authorization);
        self::assertTrue($authorization->executionAllowed());
    }

    public function testReviewOnlyAuthorizationCannotExecute(): void
    {
        $plan = (new MigrationSelector())->select(
            $this->registry(),
            new MigrationHistory(),
            new MigrationRequest(MigrationDirection::up(), MigrationExecutionMode::apply()),
        );
        $authorization = new MigrationExecutionAuthorization(
            'review-only',
            $plan->fingerprint(),
            MigrationDirection::up(),
            MigrationExecutionMode::apply(),
            false,
        );

        $this->expectException(MigrationAuthorizationMismatchException::class);
        (new MigrationExecutionAuthorizer())->assertAuthorized($plan, $authorization);
    }

    private function registry(): MigrationRegistry
    {
        return new MigrationRegistry([
            $this->descriptor('audit.create', [new MigrationId('users.create')]),
            $this->descriptor('foundation.prepare'),
            $this->descriptor('users.create', [new MigrationId('foundation.prepare')]),
        ]);
    }

    /** @param list<MigrationId> $dependencies */
    private function descriptor(string $id, array $dependencies = [], bool $reversible = true): MigrationDescriptor
    {
        return new MigrationDescriptor(
            new MigrationId($id),
            MigrationChecksum::sha256($id),
            new MigrationVersion(match ($id) {
                'foundation.prepare' => '1.0.0',
                'users.create' => '1.1.0',
                'audit.create' => '1.2.0',
                default => '2.0.0',
            }),
            $dependencies,
            $reversible,
            ['core'],
        );
    }

    private function record(string $id): MigrationHistoryRecord
    {
        return new MigrationHistoryRecord(
            new MigrationId($id),
            MigrationChecksum::sha256($id),
            MigrationHistoryStatus::applied(),
            new DateTimeImmutable('2026-07-30T12:00:00+00:00'),
        );
    }
}
