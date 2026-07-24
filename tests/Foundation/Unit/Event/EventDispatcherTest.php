<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Event\EventDispatcher;
use Sif\Foundation\Event\ListenerProvider;
use Sif\Foundation\Tests\Fixtures\Event\ParentEvent;
use Sif\Foundation\Tests\Fixtures\Event\StoppableTestEvent;

final class EventDispatcherTest extends TestCase
{
    public function testDispatchesSynchronouslyAndReturnsSameEvent(): void
    {
        $provider = new ListenerProvider();
        $called = false;
        $provider->add(ParentEvent::class, static function (object $event) use (&$called): void { $called = true; });
        $dispatcher = new EventDispatcher($provider);
        $event = new ParentEvent();

        self::assertSame($event, $dispatcher->dispatch($event));
        self::assertTrue($called);
    }

    public function testStopsBeforeFollowingListener(): void
    {
        $provider = new ListenerProvider();
        $log = [];
        $provider->add(StoppableTestEvent::class, static function (object $event) use (&$log): void {
            self::assertInstanceOf(StoppableTestEvent::class, $event);
            $log[] = 'first';
            $event->stop();
        }, 10);
        $provider->add(StoppableTestEvent::class, static function (object $event) use (&$log): void { $log[] = 'second'; });

        (new EventDispatcher($provider))->dispatch(new StoppableTestEvent());

        self::assertSame(['first'], $log);
    }

    public function testAlreadyStoppedEventInvokesNoListeners(): void
    {
        $provider = new ListenerProvider();
        $called = false;
        $provider->add(StoppableTestEvent::class, static function (object $event) use (&$called): void { $called = true; });
        $event = new StoppableTestEvent();
        $event->stop();

        (new EventDispatcher($provider))->dispatch($event);

        self::assertFalse($called);
    }

    public function testListenerExceptionPropagatesUnchanged(): void
    {
        $provider = new ListenerProvider();
        $exception = new RuntimeException('listener failed');
        $provider->add(ParentEvent::class, static function (object $event) use ($exception): void { throw $exception; });

        try {
            (new EventDispatcher($provider))->dispatch(new ParentEvent());
            self::fail('Expected listener exception was not thrown.');
        } catch (RuntimeException $caught) {
            self::assertSame($exception, $caught);
        }
    }

    public function testEventWithoutListenersIsReturnedUnchanged(): void
    {
        $dispatcher = new EventDispatcher(new ListenerProvider());
        $event = new ParentEvent();

        self::assertSame($event, $dispatcher->dispatch($event));
    }
}
