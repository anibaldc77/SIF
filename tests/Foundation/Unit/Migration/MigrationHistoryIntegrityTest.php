<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Migration;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Migration\Exceptions\InvalidMigrationHistoryException;
use Sif\Foundation\Migration\Exceptions\InvalidMigrationHistoryRecordException;
use Sif\Foundation\Migration\Exceptions\MigrationIntegrityViolationException;
use Sif\Foundation\Migration\History\MigrationHistory;
use Sif\Foundation\Migration\History\MigrationHistoryRecord;
use Sif\Foundation\Migration\History\MigrationHistoryStatus;
use Sif\Foundation\Migration\History\MigrationIntegrityChecker;
use Sif\Foundation\Migration\MigrationChecksum;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationId;
use Sif\Foundation\Migration\MigrationVersion;
use Sif\Foundation\Migration\Registry\MigrationRegistry;

final class MigrationHistoryIntegrityTest extends TestCase
{
    public function testHistoryRecordPreservesCanonicalSafeSummary(): void
    {
        $record = $this->record('users.create', 'users:create', '20260730-01');

        self::assertSame('users.create', $record->id()->value());
        self::assertSame('applied', $record->status()->value());
        self::assertSame('2026-07-30T12:00:00+00:00', $record->recordedAt()->format(DATE_ATOM));
        self::assertSame('20260730-01', $record->batch());
        self::assertSame('1.0.0', $record->version()?->value());
        self::assertSame('users.create', $record->summary()['id']);
    }

    public function testHistoryRejectsUnsafeBatchToken(): void
    {
        $this->expectException(InvalidMigrationHistoryRecordException::class);
        $this->record('users.create', 'users:create', 'unsafe batch');
    }

    public function testHistoryRejectsDuplicateMigrationIds(): void
    {
        $this->expectException(InvalidMigrationHistoryException::class);
        new MigrationHistory([
            $this->record('users.create', 'users:create'),
            $this->record('users.create', 'users:create'),
        ]);
    }

    public function testHistoryLookupAndIdentifiersAreDeterministic(): void
    {
        $history = new MigrationHistory([
            $this->record('z.last', 'z:last'),
            $this->record('a.first', 'a:first'),
        ]);

        self::assertSame(['a.first', 'z.last'], $history->identifiers());
        self::assertSame('a.first', $history->find(new MigrationId('a.first'))?->id()->value());
        self::assertNull($history->find(new MigrationId('A.first')));
        self::assertSame(2, $history->count());
    }

    public function testIntegrityReportSeparatesPendingMissingAndModifiedMigrations(): void
    {
        $registry = new MigrationRegistry([
            $this->descriptor('users.create', 'users:create'),
            $this->descriptor('audit.create', 'audit:create'),
            $this->descriptor('roles.create', 'roles:create'),
        ]);
        $history = new MigrationHistory([
            $this->record('users.create', 'users:modified'),
            $this->record('legacy.orphaned', 'legacy:orphaned'),
            new MigrationHistoryRecord(
                new MigrationId('roles.create'),
                MigrationChecksum::sha256('roles:create'),
                MigrationHistoryStatus::rolledBack(),
                new DateTimeImmutable('2026-07-30T12:00:00+00:00'),
            ),
        ]);

        $report = (new MigrationIntegrityChecker())->inspect($registry, $history);

        self::assertFalse($report->isValid());
        self::assertSame(['legacy.orphaned'], $report->missingFromRegistry());
        self::assertSame(['users.create'], $report->checksumMismatches());
        self::assertSame(['audit.create', 'roles.create'], $report->pending());
    }

    public function testIntegrityReportIsValidWhenAppliedHistoryMatchesRegistry(): void
    {
        $registry = new MigrationRegistry([$this->descriptor('users.create', 'users:create')]);
        $history = new MigrationHistory([$this->record('users.create', 'users:create')]);

        $report = (new MigrationIntegrityChecker())->inspect($registry, $history);

        self::assertTrue($report->isValid());
        self::assertSame([], $report->missingFromRegistry());
        self::assertSame([], $report->checksumMismatches());
        self::assertSame([], $report->pending());
    }

    public function testAssertValidRaisesTypedIntegrityViolationWithoutLeakingChecksums(): void
    {
        $registry = new MigrationRegistry([$this->descriptor('users.create', 'users:create')]);
        $history = new MigrationHistory([$this->record('users.create', 'tampered')]);

        $this->expectException(MigrationIntegrityViolationException::class);
        $this->expectExceptionMessage('1 checksum mismatches');
        (new MigrationIntegrityChecker())->assertValid($registry, $history);
    }

    private function descriptor(string $id, string $payload): MigrationDescriptor
    {
        return new MigrationDescriptor(
            new MigrationId($id),
            MigrationChecksum::sha256($payload),
            new MigrationVersion('1.0.0'),
            reversible: true,
        );
    }

    private function record(string $id, string $payload, ?string $batch = null): MigrationHistoryRecord
    {
        return new MigrationHistoryRecord(
            new MigrationId($id),
            MigrationChecksum::sha256($payload),
            MigrationHistoryStatus::applied(),
            new DateTimeImmutable('2026-07-30T12:00:00+00:00'),
            new MigrationVersion('1.0.0'),
            $batch,
        );
    }
}
