<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Persistence;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Exceptions\ConcurrencyConflictException;
use Sif\Foundation\Exceptions\ConnectionFailureException;
use Sif\Foundation\Exceptions\MappingFailureException;
use Sif\Foundation\Exceptions\PersistenceException;
use Sif\Foundation\Exceptions\QueryFailureException;
use Sif\Foundation\Exceptions\RepositoryFailureException;
use Sif\Foundation\Exceptions\TransactionFailureException;
use Sif\Foundation\Exceptions\UnitOfWorkFailureException;
use Sif\Foundation\Exceptions\UnsupportedPersistenceCapabilityException;
use Sif\Foundation\Persistence\PersistenceCapabilities;
use Sif\Foundation\Persistence\PersistenceCapability;
use Sif\Foundation\Persistence\PersistenceCapabilityGuard;
use Sif\Foundation\Persistence\PersistenceFailureKind;
use Sif\Foundation\Tests\Fixtures\Persistence\CapabilityAwarePersistenceAdapter;

final class PersistenceCapabilitiesAndFailuresTest extends TestCase
{
    public function testCapabilitiesAreUniqueAndDeterministicallyOrdered(): void
    {
        $capabilities = PersistenceCapabilities::of([
            PersistenceCapability::Sorting,
            PersistenceCapability::Transactions,
            PersistenceCapability::Sorting,
            PersistenceCapability::Projection,
        ]);

        self::assertSame(3, $capabilities->count());
        self::assertSame(
            [
                PersistenceCapability::Projection,
                PersistenceCapability::Sorting,
                PersistenceCapability::Transactions,
            ],
            $capabilities->all(),
        );
    }

    public function testCapabilitiesSupportImmutableAdditionAndRemoval(): void
    {
        $base = PersistenceCapabilities::none();
        $withTransactions = $base->with(
            PersistenceCapability::Transactions,
        );
        $withoutTransactions = $withTransactions->without(
            PersistenceCapability::Transactions,
        );

        self::assertTrue($base->isEmpty());
        self::assertFalse(
            $base->supports(PersistenceCapability::Transactions),
        );
        self::assertTrue(
            $withTransactions->supports(
                PersistenceCapability::Transactions,
            ),
        );
        self::assertTrue($withoutTransactions->isEmpty());
    }

    public function testGuardAcceptsSupportedCapability(): void
    {
        $provider = new CapabilityAwarePersistenceAdapter(
            PersistenceCapabilities::of([
                PersistenceCapability::Transactions,
            ]),
        );

        (new PersistenceCapabilityGuard())->require(
            $provider,
            PersistenceCapability::Transactions,
        );

        self::assertTrue(true);
    }

    public function testGuardRejectsUnsupportedCapabilityWithTypedFailure(): void
    {
        $provider = new CapabilityAwarePersistenceAdapter(
            PersistenceCapabilities::none(),
        );

        try {
            (new PersistenceCapabilityGuard())->require(
                $provider,
                PersistenceCapability::StreamingResults,
            );

            self::fail('Expected unsupported capability exception.');
        } catch (UnsupportedPersistenceCapabilityException $failure) {
            self::assertSame(
                PersistenceCapability::StreamingResults,
                $failure->capability(),
            );
            self::assertSame(
                $provider::class,
                $failure->providerType(),
            );
            self::assertSame(
                PersistenceFailureKind::UnsupportedCapability,
                $failure->kind(),
            );
            self::assertSame(
                'streaming_results',
                $failure->operation(),
            );
        }
    }

    public function testBasePersistenceFailurePreservesCauseAndOperation(): void
    {
        $cause = new RuntimeException('driver detail');

        $failure = new PersistenceException(
            message: 'Persistence operation failed.',
            kind: PersistenceFailureKind::Unknown,
            operation: 'custom.operation',
            cause: $cause,
        );

        self::assertSame(
            PersistenceFailureKind::Unknown,
            $failure->kind(),
        );
        self::assertSame('custom.operation', $failure->operation());
        self::assertSame($cause, $failure->cause());
        self::assertSame($cause, $failure->getPrevious());
        self::assertSame(
            'Persistence operation failed.',
            $failure->getMessage(),
        );
    }

    /**
     * @return iterable<string, array{PersistenceException, PersistenceFailureKind}>
     */
    public static function typedFailureProvider(): iterable
    {
        yield 'connection' => [
            new ConnectionFailureException('Connection failed.', 'connect'),
            PersistenceFailureKind::Connection,
        ];

        yield 'transaction' => [
            new TransactionFailureException('Transaction failed.', 'commit'),
            PersistenceFailureKind::Transaction,
        ];

        yield 'query' => [
            new QueryFailureException('Query failed.', 'query'),
            PersistenceFailureKind::Query,
        ];

        yield 'mapping' => [
            new MappingFailureException('Mapping failed.', 'hydrate'),
            PersistenceFailureKind::Mapping,
        ];

        yield 'concurrency' => [
            new ConcurrencyConflictException('Conflict.', 'save'),
            PersistenceFailureKind::Concurrency,
        ];

        yield 'repository' => [
            new RepositoryFailureException('Repository failed.', 'find'),
            PersistenceFailureKind::Repository,
        ];

        yield 'unit-of-work' => [
            new UnitOfWorkFailureException('Commit failed.', 'commit'),
            PersistenceFailureKind::UnitOfWork,
        ];
    }

    /**
     * @dataProvider typedFailureProvider
     */
    public function testTypedFailuresExposeStableKind(
        PersistenceException $failure,
        PersistenceFailureKind $expectedKind,
    ): void {
        self::assertSame($expectedKind, $failure->kind());
        self::assertNotNull($failure->operation());
    }

    public function testTypedFailureMayPreserveOriginalCause(): void
    {
        $cause = new RuntimeException('pdo error');

        $failure = new QueryFailureException(
            message: 'Unable to execute query.',
            operation: 'repository.query',
            cause: $cause,
        );

        self::assertSame($cause, $failure->cause());
        self::assertSame($cause, $failure->getPrevious());
        self::assertStringNotContainsString(
            'pdo error',
            $failure->getMessage(),
        );
    }
}
