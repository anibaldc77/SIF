<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Logging;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Logging\Clock\FrozenClock;
use Sif\Foundation\Logging\Factory\LogRecordFactory;
use Sif\Foundation\Logging\Filtering\AcceptAllLogRecordFilter;
use Sif\Foundation\Logging\Handling\InMemoryLogHandler;
use Sif\Foundation\Logging\Handling\NullEmergencyLogReporter;
use Sif\Foundation\Logging\LogChannel;
use Sif\Foundation\Logging\LogLevel;
use Sif\Foundation\Logging\LogTimestamp;
use Sif\Foundation\Logging\Normalization\BoundedStructuredValueNormalizer;
use Sif\Foundation\Logging\Orchestration\StructuredLogger;
use Sif\Foundation\Logging\Planning\LoggingPlan;
use Sif\Foundation\Logging\Processing\AttributeEnricherProcessor;
use Sif\Foundation\Logging\Redaction\RecursiveSecretRedactor;
use Sif\Foundation\Logging\Routing\LogRoute;
use Sif\Foundation\Logging\Routing\LogRouter;

final class LoggingOrchestrationTest extends TestCase
{
    public function testLoggerCreatesProcessesAndDispatchesARecord(): void
    {
        [$logger, $handler] = $this->logger(new AttributeEnricherProcessor(['environment' => 'test']));

        $result = $logger->warning('Module {module} failed', ['module' => 'billing', 'password' => 'secret']);

        self::assertTrue($result->succeeded());
        self::assertSame(1, $handler->count());
        self::assertSame('warning', $result->record()->level()->value());
        self::assertSame('runtime', $result->record()->channel()->value());
        self::assertSame('test', $result->record()->attributes()['environment']);
        self::assertSame('[redacted]', $result->record()->attributes()['password']);
        self::assertSame($result->record(), $handler->records()[0]);
    }

    public function testExplicitChannelAndRecordIdentifierOverrideDefaults(): void
    {
        [$logger] = $this->logger();

        $result = $logger->log(
            LogLevel::info(),
            'Module started',
            channel: new LogChannel('runtime.module'),
            recordId: 'record-42',
        );

        self::assertSame('runtime.module', $result->record()->channel()->value());
        self::assertSame('record-42', $result->record()->recordId());
    }

    public function testConvenienceMethodsMapToCanonicalLevels(): void
    {
        [$logger, $handler] = $this->logger();

        $logger->debug('debug');
        $logger->info('info');
        $logger->notice('notice');
        $logger->warning('warning');
        $logger->error('error');
        $logger->critical('critical');
        $logger->alert('alert');
        $logger->emergency('emergency');

        self::assertSame(
            ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'],
            array_map(static fn ($record): string => $record->level()->value(), $handler->records()),
        );
    }

    public function testThrowableIsProjectedByTheFactory(): void
    {
        [$logger] = $this->logger();

        $result = $logger->error('Failure', throwable: new RuntimeException('boom', 9));

        self::assertNotNull($result->record()->throwable());
        $metadata = $result->record()->throwable();
        self::assertNotNull($metadata);
        self::assertSame(RuntimeException::class, $metadata->type());
        self::assertSame('boom', $metadata->message());
        self::assertSame(9, $metadata->code());
    }

    public function testLoggingPlanDefaultsToAnEmptyProcessorPipeline(): void
    {
        [$logger, $handler] = $this->logger();

        $result = $logger->info('Unchanged', ['value' => 7]);

        self::assertSame(['value' => 7], $result->record()->attributes());
        self::assertSame($result->record(), $handler->records()[0]);
    }

    public function testLoggingPlanWithMethodsReturnNewPlans(): void
    {
        [$plan] = $this->plan();
        $processor = new AttributeEnricherProcessor(['scope' => 'new']);

        $changedChannel = $plan->withDefaultChannel(new LogChannel('runtime.changed'));
        $changedProcessor = $plan->withProcessor($processor);

        self::assertNotSame($plan, $changedChannel);
        self::assertNotSame($plan, $changedProcessor);
        self::assertSame('runtime', $plan->defaultChannel()->value());
        self::assertSame('runtime.changed', $changedChannel->defaultChannel()->value());
        self::assertSame($processor, $changedProcessor->processor());
    }

    public function testDispatchFailuresAreReturnedWithoutEscapingTheLogger(): void
    {
        $factory = $this->factory();
        $handler = new class implements \Sif\Foundation\Logging\Contracts\LogHandlerInterface {
            public function handle(\Sif\Foundation\Logging\LogRecord $record): void
            {
                throw new RuntimeException('handler failed');
            }
        };
        $router = new LogRouter([
            new LogRoute('failing.route', new AcceptAllLogRecordFilter(), $handler),
        ], new NullEmergencyLogReporter());
        $logger = new StructuredLogger(new LoggingPlan($factory, $router, new LogChannel('runtime')));

        $result = $logger->error('Failure');

        self::assertFalse($result->succeeded());
        self::assertSame(1, $result->dispatchReport()->failureCount());
        self::assertSame('failing.route', $result->dispatchReport()->failures()[0]->route());
    }

    /**
     * @return array{0: StructuredLogger, 1: InMemoryLogHandler}
     */
    private function logger(?\Sif\Foundation\Logging\Contracts\LogRecordProcessorInterface $processor = null): array
    {
        [$plan, $handler] = $this->plan($processor);

        return [new StructuredLogger($plan), $handler];
    }

    /**
     * @return array{0: LoggingPlan, 1: InMemoryLogHandler}
     */
    private function plan(?\Sif\Foundation\Logging\Contracts\LogRecordProcessorInterface $processor = null): array
    {
        $handler = new InMemoryLogHandler();
        $router = new LogRouter([
            new LogRoute('runtime.all', new AcceptAllLogRecordFilter(), $handler),
        ], new NullEmergencyLogReporter());

        return [new LoggingPlan($this->factory(), $router, new LogChannel('runtime'), $processor), $handler];
    }

    private function factory(): LogRecordFactory
    {
        return new LogRecordFactory(
            new FrozenClock(new LogTimestamp(new DateTimeImmutable('2026-07-28T20:30:00.123456-03:00'))),
            new BoundedStructuredValueNormalizer(),
            new RecursiveSecretRedactor(),
        );
    }
}
