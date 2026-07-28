<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Logging;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Logging\Clock\FrozenClock;
use Sif\Foundation\Logging\Contracts\LogRecordProcessorInterface;
use Sif\Foundation\Logging\Exceptions\LogProcessorException;
use Sif\Foundation\Logging\Factory\LogRecordFactory;
use Sif\Foundation\Logging\LogChannel;
use Sif\Foundation\Logging\LogLevel;
use Sif\Foundation\Logging\LogRecord;
use Sif\Foundation\Logging\LogTimestamp;
use Sif\Foundation\Logging\Normalization\BoundedStructuredValueNormalizer;
use Sif\Foundation\Logging\Processing\AttributeEnricherProcessor;
use Sif\Foundation\Logging\Processing\LogRecordProcessorPipeline;
use Sif\Foundation\Logging\Redaction\RecursiveSecretRedactor;
use Sif\Foundation\Logging\Rendering\PlaceholderMessageRenderer;
use Sif\Foundation\Logging\Serialization\CanonicalStructuredValueSerializer;

final class LoggingRecordPipelineTest extends TestCase
{
    public function testFactoryCreatesImmutableNormalizedAndRedactedRecord(): void
    {
        $record = $this->factory()->create(
            LogLevel::warning(),
            new LogChannel('runtime.module'),
            'Module {module} failed',
            ['module' => 'billing', 'password' => 'secret', 'object' => new \stdClass()],
            new RuntimeException('failure', 17),
            'record-1',
        );

        self::assertSame('2026-07-28T18:30:00.123456Z', $record->timestamp()->toCanonicalString());
        self::assertSame('billing', $record->attributes()['module']);
        self::assertSame('[redacted]', $record->attributes()['password']);
        self::assertSame('[object:stdClass]', $record->attributes()['object']);
        self::assertSame(RuntimeException::class, $record->throwable()?->type());
        self::assertSame('record-1', $record->recordId());
    }

    public function testFactoryAcceptsMessageObjectsAndNoThrowable(): void
    {
        $record = $this->factory()->create(
            LogLevel::info(),
            new LogChannel('runtime'),
            new \Sif\Foundation\Logging\LogMessage('Ready'),
        );

        self::assertSame('Ready', $record->message()->template());
        self::assertNull($record->throwable());
    }

    public function testRendererResolvesScalarStructuredAndDottedPlaceholders(): void
    {
        $record = $this->factory()->create(
            LogLevel::info(),
            new LogChannel('runtime'),
            'User {user.name} active={active} quota={quota} roles={roles}',
            ['user' => ['name' => 'Ana'], 'active' => true, 'quota' => 1.0, 'roles' => ['admin', 'editor']],
        );

        $rendered = $this->renderer()->render($record);

        self::assertSame('User Ana active=true quota=1.0 roles=["admin","editor"]', $rendered->rendered());
        self::assertTrue($rendered->isComplete());
    }

    public function testRendererPreservesMissingPlaceholdersAndReportsThem(): void
    {
        $record = $this->factory()->create(
            LogLevel::notice(),
            new LogChannel('runtime'),
            'Known {known}; missing {missing}',
            ['known' => 'value'],
        );

        $rendered = $this->renderer()->render($record);

        self::assertSame('Known value; missing {missing}', $rendered->rendered());
        self::assertSame(['missing'], $rendered->unresolvedPlaceholders());
        self::assertFalse($rendered->isComplete());
    }

    public function testRendererUsesAlreadyRedactedValues(): void
    {
        $record = $this->factory()->create(
            LogLevel::info(),
            new LogChannel('runtime'),
            'Credential {token}',
            ['token' => 'private'],
        );

        self::assertSame('Credential [redacted]', $this->renderer()->render($record)->rendered());
    }

    public function testProcessorPipelinePreservesDeclaredOrder(): void
    {
        $record = $this->baseRecord();
        $pipeline = new LogRecordProcessorPipeline([
            new AttributeEnricherProcessor(['step' => 'first']),
            new AttributeEnricherProcessor(['step' => 'second'], overwrite: true),
            new AttributeEnricherProcessor(['tail' => true]),
        ]);

        $processed = $pipeline->process($record);

        self::assertSame(['base' => true, 'step' => 'second', 'tail' => true], $processed->attributes());
        self::assertCount(3, $pipeline->processors());
    }

    public function testNonOverwritingEnricherPreservesExistingAttributes(): void
    {
        $processed = (new AttributeEnricherProcessor(['base' => false, 'extra' => 1]))->process($this->baseRecord());

        self::assertSame(['base' => true, 'extra' => 1], $processed->attributes());
    }

    public function testEmptyPipelineReturnsOriginalRecord(): void
    {
        $record = $this->baseRecord();
        self::assertSame($record, (new LogRecordProcessorPipeline())->process($record));
    }

    public function testProcessorFailureIsWrappedWithPositionAndCause(): void
    {
        $cause = new RuntimeException('processor failed');
        $pipeline = new LogRecordProcessorPipeline([
            new AttributeEnricherProcessor(['first' => true]),
            new FailingProcessor($cause),
        ]);

        try {
            $pipeline->process($this->baseRecord());
            self::fail('Expected processor exception was not thrown.');
        } catch (LogProcessorException $exception) {
            self::assertStringContainsString('position 1', $exception->getMessage());
            self::assertSame($cause, $exception->getPrevious());
        }
    }

    private function factory(): LogRecordFactory
    {
        return new LogRecordFactory(
            new FrozenClock(new LogTimestamp(new DateTimeImmutable('2026-07-28T18:30:00.123456Z'))),
            new BoundedStructuredValueNormalizer(),
            new RecursiveSecretRedactor(),
        );
    }

    private function renderer(): PlaceholderMessageRenderer
    {
        return new PlaceholderMessageRenderer(new CanonicalStructuredValueSerializer());
    }

    private function baseRecord(): LogRecord
    {
        return $this->factory()->create(
            LogLevel::debug(),
            new LogChannel('runtime'),
            'Base',
            ['base' => true],
        );
    }
}

final readonly class FailingProcessor implements LogRecordProcessorInterface
{
    public function __construct(private RuntimeException $cause)
    {
    }

    public function process(LogRecord $record): LogRecord
    {
        throw $this->cause;
    }
}
