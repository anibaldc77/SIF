<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Logging;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ExecutionContext;
use Sif\Foundation\Logging\Context\ScopedLogAttributes;
use Sif\Foundation\Logging\Contracts\LogRecordProcessorInterface;
use Sif\Foundation\Logging\LogChannel;
use Sif\Foundation\Logging\LogLevel;
use Sif\Foundation\Logging\LogMessage;
use Sif\Foundation\Logging\LogRecord;
use Sif\Foundation\Logging\LogTimestamp;
use Sif\Foundation\Logging\Processing\AttributeEnricherProcessor;
use Sif\Foundation\Logging\Processing\CompositeLogRecordProcessor;
use Sif\Foundation\Logging\Processing\ExecutionContextEnricherProcessor;
use Sif\Foundation\Logging\Processing\ScopedAttributeEnricherProcessor;

final class LoggingContextEnrichmentTest extends TestCase
{
    public function testScopedAttributesProvideNestedAndPrefixedViews(): void
    {
        $scope = new ScopedLogAttributes('request', ['method' => 'GET', 'route' => '/health']);

        self::assertSame(['request' => ['method' => 'GET', 'route' => '/health']], $scope->nested());
        self::assertSame(['request.method' => 'GET', 'request.route' => '/health'], $scope->prefixed());
    }

    public function testScopedAttributesRejectInvalidScope(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ScopedLogAttributes('Request Context', []);
    }

    public function testExecutionContextIsProjectedUnderDedicatedScope(): void
    {
        $processed = (new ExecutionContextEnricherProcessor($this->context()))->process($this->record());
        $context = $processed->attributes()['execution_context'];

        self::assertIsArray($context);
        self::assertSame('ctx-1', $context['context_id']);
        self::assertSame('corr-1', $context['correlation_id']);
        self::assertSame('user-7', $context['actor_id']);
        self::assertSame(['ip' => '127.0.0.1'], $context['attributes']);
    }

    public function testExecutionContextCanExcludeCustomAttributes(): void
    {
        $processed = (new ExecutionContextEnricherProcessor($this->context(), includeCustomAttributes: false))
            ->process($this->record());

        $context = $processed->attributes()['execution_context'];
        self::assertIsArray($context);
        self::assertArrayNotHasKey('attributes', $context);
    }

    public function testExistingExecutionContextScopeWinsByDefault(): void
    {
        $record = $this->record(['execution_context' => ['context_id' => 'existing']]);
        $processed = (new ExecutionContextEnricherProcessor($this->context()))->process($record);

        $context = $processed->attributes()['execution_context'];
        self::assertIsArray($context);
        self::assertSame('existing', $context['context_id']);
    }

    public function testExecutionContextScopeCanBeExplicitlyOverwritten(): void
    {
        $record = $this->record(['execution_context' => ['context_id' => 'existing']]);
        $processed = (new ExecutionContextEnricherProcessor($this->context(), overwrite: true))->process($record);

        $context = $processed->attributes()['execution_context'];
        self::assertIsArray($context);
        self::assertSame('ctx-1', $context['context_id']);
    }

    public function testScopedEnricherKeepsUnrelatedAttributes(): void
    {
        $processed = (new ScopedAttributeEnricherProcessor(
            new ScopedLogAttributes('request', ['method' => 'POST']),
        ))->process($this->record(['base' => true]));

        self::assertSame(true, $processed->attributes()['base']);
        $request = $processed->attributes()['request'];
        self::assertIsArray($request);
        self::assertSame('POST', $request['method']);
    }

    public function testCompositeProcessorPreservesCompositionOrder(): void
    {
        $composite = new CompositeLogRecordProcessor('runtime.default', [
            new AttributeEnricherProcessor(['step' => 'first']),
            new AttributeEnricherProcessor(['step' => 'second'], overwrite: true),
        ]);

        $processed = $composite->process($this->record());

        self::assertSame('runtime.default', $composite->name());
        self::assertCount(2, $composite->processors());
        self::assertSame('second', $processed->attributes()['step']);
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(
            new ContextId('ctx-1'),
            new ContextId('corr-1'),
            new DateTimeImmutable('2026-07-28T20:00:00.000000-03:00'),
            new ContextAttributes(['ip' => '127.0.0.1']),
            actorId: 'user-7',
            tenantId: 'tenant-2',
            operation: 'module.boot',
            source: 'cli',
            locale: 'es_AR',
            timezone: 'America/Argentina/Mendoza',
        );
    }

    /** @param array<string, mixed> $attributes */
    private function record(array $attributes = []): LogRecord
    {
        return new LogRecord(
            new LogTimestamp(new DateTimeImmutable('2026-07-28T23:00:00.000000Z')),
            LogLevel::info(),
            new LogChannel('runtime'),
            new LogMessage('Context enrichment'),
            $attributes,
        );
    }
}
