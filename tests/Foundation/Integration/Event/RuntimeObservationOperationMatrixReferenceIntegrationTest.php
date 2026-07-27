<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Integration\Event;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Event\EventDispatcher;
use Sif\Foundation\Event\ListenerProvider;
use Sif\Foundation\Event\Observation\InMemoryObservationFailureReporter;
use Sif\Foundation\Event\Observation\ObservationComposer;
use Sif\Foundation\Event\Observation\ObservationLifecycleFacade;
use Sif\Foundation\Events\RuntimeOperationCompleted;
use Sif\Foundation\Contracts\KernelInterface;
use Sif\Foundation\Framework;

final class RuntimeObservationOperationMatrixReferenceIntegrationTest extends TestCase
{
    public function testBootMatrixEntryUsesIndependentApplicationAndPreservesAuthority(): void
    {
        $application = Framework::create();
        $operations = [];
        $results = [];
        $reporter = new InMemoryObservationFailureReporter();
        $facade = $this->createFacade($application->kernel(), $reporter, $operations, $results);

        $result = $facade->boot($application);

        self::assertTrue($result->succeeded());
        self::assertSame('booted', $application->runtime()->state()->value);
        self::assertSame(['boot'], $operations);
        self::assertSame([$result], $results);
        self::assertSame(0, $reporter->count());
    }

    public function testRunShutdownMatrixEntryPreservesOperationOrderAndResults(): void
    {
        $application = Framework::create();
        $operations = [];
        $results = [];
        $reporter = new InMemoryObservationFailureReporter();
        $facade = $this->createFacade($application->kernel(), $reporter, $operations, $results);

        $runResult = $facade->run($application);
        $shutdownResult = $facade->shutdown($application);

        self::assertTrue($runResult->succeeded());
        self::assertTrue($shutdownResult->succeeded());
        self::assertSame('stopped', $application->runtime()->state()->value);
        self::assertSame(['run', 'shutdown'], $operations);
        self::assertSame([$runResult, $shutdownResult], $results);
        self::assertSame(0, $reporter->count());
    }

    public function testOperationMatrixRemainsExplicitAndUsesSeparateRuntimeGraphs(): void
    {
        $bootApplication = Framework::create();
        $runApplication = Framework::create();

        $bootFacade = new ObservationLifecycleFacade(
            $bootApplication->kernel(),
            ObservationComposer::isolated(
                new EventDispatcher(new ListenerProvider()),
                new InMemoryObservationFailureReporter(),
            ),
        );
        $runFacade = new ObservationLifecycleFacade(
            $runApplication->kernel(),
            ObservationComposer::isolated(
                new EventDispatcher(new ListenerProvider()),
                new InMemoryObservationFailureReporter(),
            ),
        );

        self::assertNotSame($bootApplication, $runApplication);
        self::assertNotSame($bootApplication->runtime(), $runApplication->runtime());
        self::assertSame($bootApplication->kernel(), $bootApplication->kernel());
        self::assertSame($runApplication->kernel(), $runApplication->kernel());
        self::assertNotSame($bootFacade, $bootApplication->kernel());
        self::assertNotSame($runFacade, $runApplication->kernel());
    }

    /**
     * @param list<string> $operations
     * @param list<object> $results
     */
    private function createFacade(
        KernelInterface $kernel,
        InMemoryObservationFailureReporter $reporter,
        array &$operations,
        array &$results,
    ): ObservationLifecycleFacade {
        $listeners = new ListenerProvider();
        $listeners->add(
            RuntimeOperationCompleted::class,
            static function (object $event) use (&$operations, &$results): void {
                if (!$event instanceof RuntimeOperationCompleted) {
                    return;
                }

                $operations[] = $event->operation()->value;
                $results[] = $event->result();
            },
        );

        return new ObservationLifecycleFacade(
            $kernel,
            ObservationComposer::isolated(new EventDispatcher($listeners), $reporter),
        );
    }
}
