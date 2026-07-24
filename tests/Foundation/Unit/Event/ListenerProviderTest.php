<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Event\ListenerProvider;
use Sif\Foundation\Exceptions\InvalidEventTypeException;
use Sif\Foundation\Tests\Fixtures\Event\ChildEvent;
use Sif\Foundation\Tests\Fixtures\Event\MarkerEvent;
use Sif\Foundation\Tests\Fixtures\Event\ParentEvent;
use Sif\Foundation\Tests\Fixtures\Event\RecordingSubscriber;

final class ListenerProviderTest extends TestCase
{
    public function testReturnsExactParentAndInterfaceListeners(): void
    {
        $provider = new ListenerProvider();
        $log = [];
        $provider->add(ChildEvent::class, static function (object $event) use (&$log): void { $log[] = 'exact'; });
        $provider->add(ParentEvent::class, static function (object $event) use (&$log): void { $log[] = 'parent'; });
        $provider->add(MarkerEvent::class, static function (object $event) use (&$log): void { $log[] = 'interface'; });

        foreach ($provider->listenersFor(new ChildEvent()) as $listener) {
            $listener(new ChildEvent());
        }

        self::assertSame(['exact', 'parent', 'interface'], $log);
    }

    public function testOrdersByPriorityThenInsertionOrder(): void
    {
        $provider = new ListenerProvider();
        $log = [];
        $provider->add(ParentEvent::class, static function (object $event) use (&$log): void { $log[] = 'normal'; });
        $provider->add(ParentEvent::class, static function (object $event) use (&$log): void { $log[] = 'high-first'; }, 10);
        $provider->add(ParentEvent::class, static function (object $event) use (&$log): void { $log[] = 'high-second'; }, 10);
        $provider->add(ParentEvent::class, static function (object $event) use (&$log): void { $log[] = 'low'; }, -10);

        $event = new ParentEvent();
        foreach ($provider->listenersFor($event) as $listener) {
            $listener($event);
        }

        self::assertSame(['high-first', 'high-second', 'normal', 'low'], $log);
    }

    public function testSubscriberRegistrationIsExplicitAndDeterministic(): void
    {
        $provider = new ListenerProvider();
        $subscriber = new RecordingSubscriber();

        self::assertSame($provider, $provider->subscribe($subscriber));
        self::assertSame(2, $provider->count());

        $event = new ChildEvent();
        foreach ($provider->listenersFor($event) as $listener) {
            $listener($event);
        }

        self::assertSame([
            'parent:' . ChildEvent::class,
            'marker:' . ChildEvent::class,
        ], $subscriber->log);
    }

    public function testRejectsUnknownEventType(): void
    {
        $provider = new ListenerProvider();

        $this->expectException(InvalidEventTypeException::class);

        /** @var class-string $unknownEventType */
        $unknownEventType = 'Sif\\Missing\\UnknownEvent';

        $provider->add($unknownEventType, static function (object $event): void {});
    }
}
