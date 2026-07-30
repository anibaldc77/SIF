<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Migration;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Migration\Adapter\InMemoryMigrationHistoryStore;
use Sif\Foundation\Migration\Adapter\InMemoryMigrationLock;
use Sif\Foundation\Migration\Adapter\InMemoryMigrationTransactionManager;
use Sif\Foundation\Migration\History\MigrationHistoryRecord;
use Sif\Foundation\Migration\History\MigrationHistoryStatus;
use Sif\Foundation\Migration\MigrationChecksum;
use Sif\Foundation\Migration\MigrationId;

final class MigrationReferenceAdapterInstallerIntegrationTest extends TestCase
{
    public function testHistoryStoreIsDeterministicAndReplaceable(): void
    {
        $store = new InMemoryMigrationHistoryStore();
        $id = new MigrationId('users.create');
        $record = new MigrationHistoryRecord(
            $id,
            new MigrationChecksum('sha256', str_repeat('a', 64)),
            MigrationHistoryStatus::applied(),
            new DateTimeImmutable('2026-07-30T10:00:00-03:00'),
        );
        $store->append($record);
        self::assertSame($record, $store->find($id));
        self::assertSame(['users.create'], array_map(static fn ($item): string => $item->id()->value(), $store->history()->records()));
        $store->remove($id);
        self::assertNull($store->find($id));
    }

    public function testLockHasExclusiveOwnershipAndSafeRelease(): void
    {
        $lock = new InMemoryMigrationLock();
        self::assertTrue($lock->acquire('owner-a'));
        self::assertFalse($lock->acquire('owner-b'));
        $lock->release('owner-b');
        self::assertSame('owner-a', $lock->owner());
        $lock->release('owner-a');
        self::assertNull($lock->owner());
    }

    public function testTransactionManagerKeepsReferenceJournal(): void
    {
        $transactions = new InMemoryMigrationTransactionManager();
        $transactions->begin();
        $transactions->commit();
        self::assertSame(['begin', 'commit'], $transactions->journal());
        self::assertFalse($transactions->active());
    }
}
