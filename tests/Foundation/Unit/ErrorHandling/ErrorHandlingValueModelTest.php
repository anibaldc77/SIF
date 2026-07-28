<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ErrorHandling;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\ErrorHandling\Clock\FrozenFailureClock;
use Sif\Foundation\ErrorHandling\FailureCategory;
use Sif\Foundation\ErrorHandling\FailureDisposition;
use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\FailureId;
use Sif\Foundation\ErrorHandling\FailureOrigin;
use Sif\Foundation\ErrorHandling\FailureSeverity;
use Sif\Foundation\ErrorHandling\FailureTimestamp;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidFailureEnvelopeException;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidFailureIdException;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidFailureOriginException;

final class ErrorHandlingValueModelTest extends TestCase
{
    public function testFailureIdentifierIsTrimmedAndPreserved(): void
    {
        self::assertSame('failure:runtime-1', (new FailureId(' failure:runtime-1 '))->value());
    }

    public function testInvalidFailureIdentifierIsRejected(): void
    {
        $this->expectException(InvalidFailureIdException::class);
        new FailureId('invalid failure');
    }

    public function testCategoryDispositionAndSeverityExposeCanonicalValues(): void
    {
        self::assertSame('dependency', FailureCategory::dependency()->value());
        self::assertSame('transient', FailureDisposition::transient()->value());
        self::assertSame('critical', FailureSeverity::critical()->value());
    }

    public function testSeverityComparisonIsDeterministic(): void
    {
        self::assertTrue(FailureSeverity::error()->isAtLeast(FailureSeverity::warning()));
        self::assertFalse(FailureSeverity::info()->isAtLeast(FailureSeverity::critical()));
    }

    public function testOriginRequiresPortableLowercaseIdentifier(): void
    {
        self::assertSame('runtime.module-boot', (new FailureOrigin('runtime.module-boot'))->value());
        $this->expectException(InvalidFailureOriginException::class);
        new FailureOrigin('Runtime Module');
    }

    public function testTimestampIsCanonicalizedToUtcWithMicroseconds(): void
    {
        $timestamp = new FailureTimestamp(new DateTimeImmutable('2026-07-28T20:30:00.123456-03:00'));
        self::assertSame('2026-07-28T23:30:00.123456Z', $timestamp->canonical());
    }

    public function testFrozenClockReturnsExactInstant(): void
    {
        $instant = new DateTimeImmutable('2026-07-28T12:00:00.000001Z');
        self::assertSame($instant, (new FrozenFailureClock($instant))->now());
    }

    public function testEnvelopePreservesOriginalThrowableAndMetadata(): void
    {
        $throwable = new InvalidArgumentException('Invalid module', 17);
        $envelope = FailureEnvelope::capture(
            new FailureId('failure-1'),
            new FrozenFailureClock(new DateTimeImmutable('2026-07-28T18:00:00Z')),
            FailureCategory::validation(),
            FailureSeverity::error(),
            FailureDisposition::invalid(),
            new FailureOrigin('runtime.module'),
            $throwable,
            ['module' => 'billing', 'attempt' => 1],
        );
        self::assertSame($throwable, $envelope->throwable());
        self::assertSame(['module' => 'billing', 'attempt' => 1], $envelope->metadata());
        self::assertSame('2026-07-28T18:00:00.000000Z', $envelope->occurredAt()->canonical());
    }

    public function testEnvelopeSummaryDoesNotExposeTraceOrThrowableState(): void
    {
        $envelope = FailureEnvelope::capture(
            new FailureId('failure-2'),
            new FrozenFailureClock(new DateTimeImmutable('2026-07-28T18:00:00Z')),
            FailureCategory::unknown(),
            FailureSeverity::warning(),
            FailureDisposition::unknown(),
            new FailureOrigin('runtime'),
            new InvalidArgumentException('Failure', 9),
        );
        self::assertSame([
            'type' => InvalidArgumentException::class,
            'message' => 'Failure',
            'code' => 9,
        ], $envelope->summary()['throwable']);
        self::assertArrayNotHasKey('trace', $envelope->summary()['throwable']);
    }

    public function testEnvelopeRejectsObjectsInMetadata(): void
    {
        $this->expectException(InvalidFailureEnvelopeException::class);
        FailureEnvelope::capture(
            new FailureId('failure-3'),
            new FrozenFailureClock(new DateTimeImmutable('2026-07-28T18:00:00Z')),
            FailureCategory::application(),
            FailureSeverity::error(),
            FailureDisposition::permanent(),
            new FailureOrigin('application'),
            new InvalidArgumentException('Failure'),
            // @phpstan-ignore argument.type
            ['object' => new \stdClass()],
        );
    }
}
