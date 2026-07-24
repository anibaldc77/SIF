<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit;

use DateTimeImmutable;
use JsonSerializable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Sif\Foundation\Events\ApplicationBooted;
use Sif\Foundation\Events\ApplicationCreated;
use Sif\Foundation\Events\ApplicationStopped;
use Sif\Foundation\Events\ApplicationStopping;
use Sif\Foundation\Events\FrameworkBooted;
use Sif\Foundation\Events\FrameworkBooting;
use Sif\Foundation\Events\KernelBooted;
use Sif\Foundation\Events\KernelBooting;
use Sif\Foundation\Framework;

final class RuntimeEventTest extends TestCase
{
    /**
     * @return iterable<string, array{
     *     class-string<FrameworkBooting|FrameworkBooted|ApplicationCreated|ApplicationBooted|KernelBooting|KernelBooted|ApplicationStopping|ApplicationStopped>,
     *     string
     * }>
     */
    public static function applicationEvents(): iterable
    {
        yield 'framework booting' => [FrameworkBooting::class, 'framework.booting'];
        yield 'framework booted' => [FrameworkBooted::class, 'framework.booted'];
        yield 'application created' => [ApplicationCreated::class, 'application.created'];
        yield 'application booted' => [ApplicationBooted::class, 'application.booted'];
        yield 'kernel booting' => [KernelBooting::class, 'kernel.booting'];
        yield 'kernel booted' => [KernelBooted::class, 'kernel.booted'];
        yield 'application stopping' => [ApplicationStopping::class, 'application.stopping'];
        yield 'application stopped' => [ApplicationStopped::class, 'application.stopped'];
    }

    /** @param class-string<FrameworkBooting|FrameworkBooted|ApplicationCreated|ApplicationBooted|KernelBooting|KernelBooted|ApplicationStopping|ApplicationStopped> $eventClass */
    #[DataProvider('applicationEvents')]
    public function testApplicationEventIsImmutableAndSafelySerializable(string $eventClass, string $eventName): void
    {
        $application = Framework::create();
        $timestamp = new DateTimeImmutable('2026-07-15T10:30:00+00:00');
        $event = new $eventClass($application, $timestamp);

        self::assertInstanceOf(JsonSerializable::class, $event);
        self::assertTrue((new ReflectionClass($event))->isReadOnly());
        self::assertSame($application, $event->application());
        self::assertSame($timestamp, $event->timestamp());

        $payload = $event->jsonSerialize();
        self::assertSame($eventName, $payload['event']);
        self::assertSame('production', $payload['environment']);
        self::assertSame('created', $payload['state']);
        self::assertSame('created', $payload['stage']);
        self::assertSame(['runtime', 'foundation', 'providers', 'lifecycle', 'configuration'], $payload['capabilities']);
        self::assertSame('2026-07-15T10:30:00+00:00', $payload['timestamp']);
        self::assertNotContains($application, $payload);
    }

    public function testConstructingEventsDoesNotDispatchOrChangeLifecycle(): void
    {
        $application = Framework::create();
        $timestamp = new DateTimeImmutable();

        foreach (self::applicationEvents() as [$eventClass]) {
            new $eventClass($application, $timestamp);
        }

        self::assertTrue($application->runtime()->isCreated());
        self::assertTrue($application->run()->succeeded());
        self::assertTrue($application->shutdown()->succeeded());
    }
}
