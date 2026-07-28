<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Logging;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Logging\Clock\FrozenClock;
use Sif\Foundation\Logging\Exceptions\InvalidLogChannelException;
use Sif\Foundation\Logging\Exceptions\InvalidLogLevelException;
use Sif\Foundation\Logging\Exceptions\InvalidLogRecordException;
use Sif\Foundation\Logging\LogChannel;
use Sif\Foundation\Logging\LogLevel;
use Sif\Foundation\Logging\LogMessage;
use Sif\Foundation\Logging\LogRecord;
use Sif\Foundation\Logging\LogTimestamp;
use Sif\Foundation\Logging\ThrowableMetadata;

final class LoggingValueModelTest extends TestCase
{
    public function testLevelsExposeDeterministicSeverityOrdering(): void
    {
        self::assertTrue(LogLevel::error()->isAtLeast(LogLevel::warning()));
        self::assertFalse(LogLevel::debug()->isAtLeast(LogLevel::info()));
        self::assertSame(600, LogLevel::emergency()->priority());
        self::assertSame('critical', (string) LogLevel::critical());
    }

    public function testUnknownLevelIsRejected(): void
    {
        $this->expectException(InvalidLogLevelException::class);
        new LogLevel('verbose');
    }

    public function testChannelUsesPortableCanonicalSyntax(): void
    {
        $channel = new LogChannel('runtime.module-boot');
        self::assertSame('runtime.module-boot', $channel->value());
        self::assertTrue($channel->equals(new LogChannel('runtime.module-boot')));
    }

    /** @dataProvider invalidChannels */
    public function testInvalidChannelsAreRejected(string $value): void
    {
        $this->expectException(InvalidLogChannelException::class);
        new LogChannel($value);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidChannels(): iterable
    {
        yield 'empty' => [''];
        yield 'uppercase' => ['Runtime'];
        yield 'space' => ['runtime boot'];
        yield 'leading separator' => ['.runtime'];
    }

    public function testMessageDiscoversStableUniquePlaceholders(): void
    {
        $message = new LogMessage('Module {module} entered {stage}; module={module}');
        self::assertSame(['module', 'stage'], $message->placeholders());
        self::assertSame('Module {module} entered {stage}; module={module}', $message->template());
    }

    public function testTimestampIsCanonicalizedToUtcAndClockIsDeterministic(): void
    {
        $timestamp = new LogTimestamp(new DateTimeImmutable('2026-07-28T15:30:00.123456-03:00'));
        $clock = new FrozenClock($timestamp);
        self::assertSame('2026-07-28T18:30:00.123456Z', $clock->now()->toCanonicalString());
        self::assertTrue($timestamp->equals($clock->now()));
    }

    public function testThrowableMetadataDoesNotExposeTraceOrObjectState(): void
    {
        $metadata = ThrowableMetadata::fromThrowable(new RuntimeException('failure', 42));
        self::assertSame(RuntimeException::class, $metadata->type());
        self::assertSame(['type' => RuntimeException::class, 'message' => 'failure', 'code' => 42], $metadata->toArray());
        self::assertArrayNotHasKey('trace', $metadata->toArray());
    }

    public function testRecordPreservesImmutableStructuredValues(): void
    {
        $attributes = ['module' => 'audit', 'attempt' => 2, 'flags' => ['safe' => true]];
        $record = new LogRecord(
            new LogTimestamp(new DateTimeImmutable('2026-07-28T18:30:00Z')),
            LogLevel::info(),
            new LogChannel('runtime.module'),
            new LogMessage('Module {module} booted'),
            $attributes,
            recordId: 'record-1',
        );
        $attributes['module'] = 'changed';
        self::assertSame('audit', $record->attributes()['module']);
        self::assertSame('record-1', $record->recordId());
    }

    public function testRecordRejectsObjectsBeforeNormalizationBoundary(): void
    {
        $this->expectException(InvalidLogRecordException::class);
        new LogRecord(
            new LogTimestamp(new DateTimeImmutable('2026-07-28T18:30:00Z')),
            LogLevel::info(),
            new LogChannel('runtime'),
            new LogMessage('Invalid attribute'),
            ['object' => new \stdClass()],
        );
    }
}
