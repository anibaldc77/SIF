<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Event\Observation;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\EventObserverInterface;
use Sif\Foundation\Event\ListenerProvider;
use Sif\Foundation\Event\Observation\CompositeEventObserver;
use Sif\Foundation\Event\Observation\IsolatedEventObserver;
use Sif\Foundation\Event\Observation\NullEventObserver;
use Sif\Foundation\Event\Observation\ObservationComposer;
use Sif\Foundation\Event\Observation\ObservationFailure;
use Sif\Foundation\Event\Observation\ObservationResult;
use Sif\Foundation\Event\Observation\ObservedKernel;
use Sif\Foundation\Event\EventDispatcher;
use Sif\Foundation\Framework;

final class ObservationCompositionTest extends TestCase
{
    public function testNullObserverReturnsSuccessfulResultForSameEvent(): void
    {
        $event = new \stdClass();
        $result = (new NullEventObserver())->observe($event);

        self::assertTrue($result->succeeded());
        self::assertSame($event, $result->event());
        self::assertNull($result->failure());
    }

    public function testCompositeObserverUsesInsertionOrder(): void
    {
        $order = [];
        $event = new \stdClass();
        $observer = ObservationComposer::combine(
            self::recordingObserver($order, 'first'),
            self::recordingObserver($order, 'second'),
            self::recordingObserver($order, 'third'),
        );

        $result = $observer->observe($event);

        self::assertTrue($result->succeeded());
        self::assertSame(['first', 'second', 'third'], $order);
        self::assertSame($event, $result->event());
    }

    public function testCompositeContinuesAfterReturnedFailureAndKeepsFirstFailure(): void
    {
        $order = [];
        $event = new \stdClass();
        $cause = new \RuntimeException('first failure');
        $observer = new CompositeEventObserver([
            self::failingObserver($order, 'first', $cause),
            self::recordingObserver($order, 'second'),
        ]);

        $result = $observer->observe($event);

        self::assertTrue($result->failed());
        self::assertSame(['first', 'second'], $order);
        self::assertSame($cause, $result->failure()?->cause());
    }

    public function testCompositeIsolatesThrownObserverAndContinues(): void
    {
        $order = [];
        $event = new \stdClass();
        $cause = new \RuntimeException('observer exploded');
        $observer = new CompositeEventObserver([
            self::throwingObserver($order, 'first', $cause),
            self::recordingObserver($order, 'second'),
        ]);

        $result = $observer->observe($event);

        self::assertTrue($result->failed());
        self::assertSame(['first', 'second'], $order);
        self::assertSame($cause, $result->failure()?->cause());
        self::assertSame($event, $result->event());
    }

    public function testComposerReturnsNullObserverForEmptyComposition(): void
    {
        self::assertInstanceOf(NullEventObserver::class, ObservationComposer::combine());
    }

    public function testComposerReturnsSingleObserverByIdentity(): void
    {
        $observer = new NullEventObserver();

        self::assertSame($observer, ObservationComposer::combine($observer));
    }

    public function testComposerReturnsCompositeForMultipleObservers(): void
    {
        self::assertInstanceOf(
            CompositeEventObserver::class,
            ObservationComposer::combine(new NullEventObserver(), new NullEventObserver()),
        );
    }

    public function testComposerCreatesIsolatedObserverAndObservedKernel(): void
    {
        $application = Framework::create();
        $provider = new ListenerProvider();
        $dispatcher = new EventDispatcher($provider);
        $observer = ObservationComposer::isolated($dispatcher);
        $kernel = ObservationComposer::kernel($application->kernel(), $observer);

        self::assertInstanceOf(IsolatedEventObserver::class, $observer);
        self::assertInstanceOf(ObservedKernel::class, $kernel);
        self::assertTrue($kernel->run($application)->succeeded());
    }

    /** @param list<string> $order */
    private static function recordingObserver(array &$order, string $name): EventObserverInterface
    {
        $record = static function () use (&$order, $name): void {
            $order[] = $name;
        };

        return new class ($record) implements EventObserverInterface {
            public function __construct(private \Closure $record)
            {
            }

            public function observe(object $event): ObservationResult
            {
                ($this->record)();

                return ObservationResult::success($event);
            }
        };
    }

    /** @param list<string> $order */
    private static function failingObserver(
        array &$order,
        string $name,
        \Throwable $cause,
    ): EventObserverInterface {
        $record = static function () use (&$order, $name): void {
            $order[] = $name;
        };

        return new class ($record, $cause) implements EventObserverInterface {
            public function __construct(
                private \Closure $record,
                private \Throwable $cause,
            ) {
            }

            public function observe(object $event): ObservationResult
            {
                ($this->record)();

                return ObservationResult::fromFailure(
                    new ObservationFailure($event, $this->cause),
                );
            }
        };
    }

    /** @param list<string> $order */
    private static function throwingObserver(
        array &$order,
        string $name,
        \Throwable $cause,
    ): EventObserverInterface {
        $record = static function () use (&$order, $name): void {
            $order[] = $name;
        };

        return new class ($record, $cause) implements EventObserverInterface {
            public function __construct(
                private \Closure $record,
                private \Throwable $cause,
            ) {
            }

            public function observe(object $event): ObservationResult
            {
                ($this->record)();

                throw $this->cause;
            }
        };
    }

}
