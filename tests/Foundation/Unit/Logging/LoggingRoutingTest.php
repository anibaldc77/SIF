<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Logging;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Logging\Contracts\EmergencyLogReporterInterface;
use Sif\Foundation\Logging\Contracts\LogHandlerInterface;
use Sif\Foundation\Logging\Filtering\AcceptAllLogRecordFilter;
use Sif\Foundation\Logging\Filtering\ChannelLogRecordFilter;
use Sif\Foundation\Logging\Filtering\CompositeLogRecordFilter;
use Sif\Foundation\Logging\Filtering\MinimumLevelLogRecordFilter;
use Sif\Foundation\Logging\Handling\InMemoryLogHandler;
use Sif\Foundation\Logging\Handling\NullEmergencyLogReporter;
use Sif\Foundation\Logging\LogChannel;
use Sif\Foundation\Logging\LogLevel;
use Sif\Foundation\Logging\LogMessage;
use Sif\Foundation\Logging\LogRecord;
use Sif\Foundation\Logging\LogTimestamp;
use Sif\Foundation\Logging\Routing\LogRoute;
use Sif\Foundation\Logging\Routing\LogRouter;
use Throwable;

final class LoggingRoutingTest extends TestCase
{
    public function testMinimumLevelFilterUsesCanonicalPriorities(): void
    {
        $filter = new MinimumLevelLogRecordFilter(LogLevel::warning());

        self::assertFalse($filter->accepts($this->record(LogLevel::info())));
        self::assertTrue($filter->accepts($this->record(LogLevel::warning())));
        self::assertTrue($filter->accepts($this->record(LogLevel::critical())));
    }

    public function testChannelFilterAcceptsOnlyDeclaredChannels(): void
    {
        $filter = new ChannelLogRecordFilter([new LogChannel('runtime'), new LogChannel('audit')]);

        self::assertTrue($filter->accepts($this->record(channel: 'runtime')));
        self::assertFalse($filter->accepts($this->record(channel: 'http')));
    }

    public function testCompositeFilterRequiresEveryFilterToAccept(): void
    {
        $filter = new CompositeLogRecordFilter([
            new MinimumLevelLogRecordFilter(LogLevel::error()),
            new ChannelLogRecordFilter([new LogChannel('runtime')]),
        ]);

        self::assertTrue($filter->accepts($this->record(LogLevel::error(), 'runtime')));
        self::assertFalse($filter->accepts($this->record(LogLevel::warning(), 'runtime')));
        self::assertFalse($filter->accepts($this->record(LogLevel::error(), 'http')));
    }

    public function testRouterDispatchesInDeclarationOrder(): void
    {
        $order = new DispatchOrderRecorder();
        $first = new OrderedLogHandler($order, 'first');
        $second = new OrderedLogHandler($order, 'second');

        $report = (new LogRouter([
            new LogRoute('first', new AcceptAllLogRecordFilter(), $first),
            new LogRoute('second', new AcceptAllLogRecordFilter(), $second),
        ], new NullEmergencyLogReporter()))->dispatch($this->record());

        self::assertSame(['first', 'second'], $order->values());
        self::assertSame(['first', 'second'], $report->handledRoutes());
        self::assertTrue($report->succeeded());
    }

    public function testFilteredRoutesAreReportedWithoutCallingHandler(): void
    {
        $handler = new InMemoryLogHandler();
        $report = (new LogRouter([
            new LogRoute('errors', new MinimumLevelLogRecordFilter(LogLevel::error()), $handler),
        ], new NullEmergencyLogReporter()))->dispatch($this->record(LogLevel::info()));

        self::assertSame(0, $handler->count());
        self::assertSame(['errors'], $report->filteredRoutes());
    }

    public function testHandlerFailureIsIsolatedAndLaterRoutesContinue(): void
    {
        $healthy = new InMemoryLogHandler();
        $failing = new class implements LogHandlerInterface {
            public function handle(LogRecord $record): void { throw new RuntimeException('sink unavailable'); }
        };
        $reporter = new RecordingEmergencyReporter();

        $report = (new LogRouter([
            new LogRoute('broken', new AcceptAllLogRecordFilter(), $failing),
            new LogRoute('healthy', new AcceptAllLogRecordFilter(), $healthy),
        ], $reporter))->dispatch($this->record());

        self::assertSame(1, $healthy->count());
        self::assertSame(1, $report->failureCount());
        self::assertSame('broken', $report->failures()[0]->route());
        self::assertSame(['broken'], $reporter->routes);
    }

    public function testEmergencyReporterFailureNeverEscapesOrRecurses(): void
    {
        $failingHandler = new class implements LogHandlerInterface {
            public function handle(LogRecord $record): void { throw new RuntimeException('primary failure'); }
        };
        $failingReporter = new class implements EmergencyLogReporterInterface {
            public function report(string $route, LogRecord $record, Throwable $failure): void
            {
                throw new RuntimeException('emergency failure');
            }
        };

        $report = (new LogRouter([
            new LogRoute('broken', new AcceptAllLogRecordFilter(), $failingHandler),
        ], $failingReporter))->dispatch($this->record());

        self::assertSame(1, $report->failureCount());
        self::assertFalse($report->succeeded());
    }

    public function testDuplicateRouteNamesAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LogRouter([
            new LogRoute('runtime', new AcceptAllLogRecordFilter(), new InMemoryLogHandler()),
            new LogRoute('runtime', new AcceptAllLogRecordFilter(), new InMemoryLogHandler()),
        ], new NullEmergencyLogReporter());
    }

    public function testInMemoryHandlerCanBeCleared(): void
    {
        $handler = new InMemoryLogHandler();
        $handler->handle($this->record());
        self::assertSame(1, $handler->count());
        $handler->clear();
        self::assertSame([], $handler->records());
    }

    private function record(?LogLevel $level = null, string $channel = 'runtime'): LogRecord
    {
        return new LogRecord(
            new LogTimestamp(new DateTimeImmutable('2026-07-28T20:00:00.000000Z')),
            $level ?? LogLevel::info(),
            new LogChannel($channel),
            new LogMessage('test'),
        );
    }
}

final class RecordingEmergencyReporter implements EmergencyLogReporterInterface
{
    /** @var list<string> */
    public array $routes = [];

    public function report(string $route, LogRecord $record, Throwable $failure): void
    {
        $this->routes[] = $route;
    }
}


final class DispatchOrderRecorder
{
    /** @var list<string> */
    private array $values = [];

    public function append(string $value): void
    {
        $this->values[] = $value;
    }

    /** @return list<string> */
    public function values(): array
    {
        return $this->values;
    }
}

final readonly class OrderedLogHandler implements LogHandlerInterface
{
    public function __construct(private DispatchOrderRecorder $recorder, private string $name)
    {
    }

    public function handle(LogRecord $record): void
    {
        $this->recorder->append($this->name);
    }
}
