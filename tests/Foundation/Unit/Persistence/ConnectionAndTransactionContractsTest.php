<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Persistence;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Exceptions\ConnectionAlreadyRegisteredException;
use Sif\Foundation\Exceptions\ConnectionNotFoundException;
use Sif\Foundation\Exceptions\InvalidConnectionNameException;
use Sif\Foundation\Exceptions\NestedTransactionNotSupportedException;
use Sif\Foundation\Persistence\ConnectionName;
use Sif\Foundation\Persistence\ConnectionRegistry;
use Sif\Foundation\Persistence\TransactionState;
use Sif\Foundation\Tests\Fixtures\Persistence\InMemoryConnection;
use Sif\Foundation\Tests\Fixtures\Persistence\InMemoryTransactionManager;

final class ConnectionAndTransactionContractsTest extends TestCase
{
    public function testConnectionNamePreservesOpaqueValueAndEquality(): void
    {
        $name = new ConnectionName('primary');

        self::assertSame('primary', $name->value());
        self::assertSame('primary', (string) $name);
        self::assertTrue($name->equals(new ConnectionName('primary')));
        self::assertFalse($name->equals(new ConnectionName('replica')));
        self::assertSame('default', ConnectionName::default()->value());
    }

    public function testConnectionNameRejectsEmptyValue(): void
    {
        $this->expectException(InvalidConnectionNameException::class);

        new ConnectionName(' ');
    }

    public function testRegistryResolvesDefaultAndNamedConnections(): void
    {
        $default = new InMemoryConnection(new ConnectionName('default'));
        $replica = new InMemoryConnection(new ConnectionName('replica'));
        $registry = new ConnectionRegistry();

        $registry->register($default);
        $registry->register($replica);

        self::assertSame($default, $registry->connection());
        self::assertSame(
            $replica,
            $registry->connection(new ConnectionName('replica')),
        );
        self::assertTrue($registry->has(new ConnectionName('default')));
        self::assertTrue($registry->has(new ConnectionName('replica')));
    }

    public function testRegistryCanChangeDefaultExplicitly(): void
    {
        $default = new InMemoryConnection(new ConnectionName('default'));
        $replica = new InMemoryConnection(new ConnectionName('replica'));
        $registry = new ConnectionRegistry();

        $registry->register($default);
        $registry->register($replica);
        $registry->useDefault(new ConnectionName('replica'));

        self::assertSame('replica', $registry->defaultConnectionName()->value());
        self::assertSame($replica, $registry->connection());
    }

    public function testRegistryRejectsDuplicateConnectionNames(): void
    {
        $registry = new ConnectionRegistry();
        $registry->register(
            new InMemoryConnection(new ConnectionName('default')),
        );

        $this->expectException(ConnectionAlreadyRegisteredException::class);

        $registry->register(
            new InMemoryConnection(new ConnectionName('default')),
        );
    }

    public function testRegistryRejectsUnknownConnections(): void
    {
        $this->expectException(ConnectionNotFoundException::class);

        (new ConnectionRegistry())->connection(
            new ConnectionName('missing'),
        );
    }

    public function testConnectionLifecycleRemainsAdapterOwned(): void
    {
        $connection = new InMemoryConnection(
            new ConnectionName('default'),
        );

        self::assertTrue($connection->isOpen());

        $connection->close();

        self::assertFalse($connection->isOpen());
    }

    public function testTransactionPreservesCallbackReturnValue(): void
    {
        $manager = new InMemoryTransactionManager();

        $result = $manager->transactional(
            static fn (): string => 'completed',
        );

        self::assertSame('completed', $result);
        self::assertSame(TransactionState::Committed, $manager->state());
        self::assertSame(0, $manager->depth());
        self::assertSame(
            [
                TransactionState::Idle,
                TransactionState::Active,
                TransactionState::Committed,
            ],
            $manager->history(),
        );
    }

    public function testTransactionRollsBackAndRethrowsSameFailure(): void
    {
        $manager = new InMemoryTransactionManager();
        $failure = new RuntimeException('transaction failure');

        $this->expectExceptionObject($failure);

        try {
            $manager->transactional(
                static function () use ($failure): never {
                    throw $failure;
                },
            );
        } finally {
            self::assertSame(
                TransactionState::RolledBack,
                $manager->state(),
            );
            self::assertSame(0, $manager->depth());
            self::assertSame(
                [
                    TransactionState::Idle,
                    TransactionState::Active,
                    TransactionState::RolledBack,
                ],
                $manager->history(),
            );
        }
    }

    public function testNestedTransactionPolicyFailsExplicitly(): void
    {
        $manager = new InMemoryTransactionManager();

        $this->expectException(
            NestedTransactionNotSupportedException::class,
        );

        $manager->transactional(
            static function () use ($manager): void {
                $manager->transactional(
                    static fn (): string => 'nested',
                );
            },
        );
    }

    public function testManagerCanExecuteAnotherTransactionAfterCompletion(): void
    {
        $manager = new InMemoryTransactionManager();

        $first = $manager->transactional(
            static fn (): int => 1,
        );

        $second = $manager->transactional(
            static fn (): int => 2,
        );

        self::assertSame(1, $first);
        self::assertSame(2, $second);
        self::assertSame(TransactionState::Committed, $manager->state());
        self::assertSame(0, $manager->depth());
    }
}
