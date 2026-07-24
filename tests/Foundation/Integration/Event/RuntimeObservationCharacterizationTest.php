<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Integration\Event;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\BootStage;
use Sif\Foundation\Event\EventDispatcher;
use Sif\Foundation\Event\ListenerProvider;
use Sif\Foundation\Event\Observation\IsolatedEventObserver;
use Sif\Foundation\Framework;
use Sif\Foundation\Runtime;
use stdClass;

final class RuntimeObservationCharacterizationTest extends TestCase
{
    public function testFailedObservationDoesNotChangeSuccessfulApplicationLifecycle(): void
    {
        $application = Framework::create();
        self::assertTrue($application->run()->succeeded());
        self::assertTrue($application->runtime()->isRunning());

        $provider = new ListenerProvider();
        $provider->add(stdClass::class, static function (object $event): void {
            self::assertInstanceOf(stdClass::class, $event);
            throw new \RuntimeException('observer failure');
        });
        $observer = new IsolatedEventObserver(new EventDispatcher($provider));

        self::assertTrue($observer->observe(new stdClass())->failed());
        self::assertTrue($application->runtime()->isRunning());
        self::assertNull($application->runtime()->failure());
        self::assertTrue($application->shutdown()->succeeded());
        self::assertTrue($application->runtime()->isStopped());
    }

    public function testFailedObservationDoesNotReplaceExistingRuntimeFailureCause(): void
    {
        $runtime = new Runtime();
        $runtimeCause = new \RuntimeException('runtime failure');
        $runtime->fail($runtimeCause, BootStage::Failed);

        $provider = new ListenerProvider();
        $provider->add(stdClass::class, static function (object $event): void {
            self::assertInstanceOf(stdClass::class, $event);
            throw new \RuntimeException('observer failure');
        });
        $observer = new IsolatedEventObserver(new EventDispatcher($provider));

        self::assertTrue($observer->observe(new stdClass())->failed());
        self::assertTrue($runtime->hasFailed());
        self::assertSame($runtimeCause, $runtime->failure());
    }

    public function testRuntimeBehaviorIsIdenticalWhenObserverIsNotComposed(): void
    {
        $first = Framework::create();
        $second = Framework::create();

        self::assertTrue($first->run()->succeeded());
        self::assertTrue($second->run()->succeeded());
        self::assertSame($first->runtime()->state(), $second->runtime()->state());
        self::assertSame($first->runtime()->stage(), $second->runtime()->stage());

        self::assertTrue($first->shutdown()->succeeded());
        self::assertTrue($second->shutdown()->succeeded());
        self::assertSame($first->runtime()->state(), $second->runtime()->state());
        self::assertSame($first->runtime()->stage(), $second->runtime()->stage());
    }
}
