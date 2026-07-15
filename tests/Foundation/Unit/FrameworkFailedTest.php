<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Sif\Foundation\BootStage;
use Sif\Foundation\Events\FrameworkFailed;
use Sif\Foundation\Runtime;

final class FrameworkFailedTest extends TestCase
{
    public function testFailureRetainsCauseButSerializesOnlySafeDiagnostics(): void
    {
        $runtime = new Runtime();
        $cause = new \RuntimeException('secret=/private/path credential=hidden');
        $runtime->fail($cause, BootStage::Failed);
        $timestamp = new DateTimeImmutable('2026-07-15T10:30:00+00:00');
        $event = new FrameworkFailed($runtime, $cause, $timestamp);

        self::assertTrue((new ReflectionClass($event))->isReadOnly());
        self::assertSame($runtime, $event->runtime());
        self::assertSame($cause, $event->cause());
        self::assertSame($timestamp, $event->timestamp());

        $payload = $event->jsonSerialize();
        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        self::assertSame('framework.failed', $payload['event']);
        self::assertSame('failed', $payload['state']);
        self::assertSame('failed', $payload['stage']);
        self::assertSame('2026-07-15T10:30:00+00:00', $payload['timestamp']);
        self::assertSame('framework.failed', $payload['diagnostic']['code']);
        self::assertSame(\RuntimeException::class, $payload['diagnostic']['type']);
        self::assertStringNotContainsString('secret', $json);
        self::assertStringNotContainsString('/private/path', $json);
        self::assertStringNotContainsString('trace', strtolower($json));
    }
}
