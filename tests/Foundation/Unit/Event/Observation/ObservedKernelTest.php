<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Event\Observation;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\BootResult;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\KernelInterface;
use Sif\Foundation\Event\Observation\ObservedKernel;
use Sif\Foundation\Event\Observation\RuntimeOperation;
use Sif\Foundation\Events\RuntimeOperationCompleted;
use Sif\Foundation\Framework;
use Sif\Foundation\Tests\Fixtures\Event\RecordingEventObserver;

final class ObservedKernelTest extends TestCase
{
    public function testRunReturnsExactDelegateResultAndObservesCompletion(): void
    {
        $application = Framework::create();
        $observer = new RecordingEventObserver();
        $kernel = new ObservedKernel($application->kernel(), $observer);

        $result = $kernel->run($application);

        self::assertTrue($result->succeeded());
        self::assertCount(1, $observer->events);
        $event = $observer->events[0];
        self::assertInstanceOf(RuntimeOperationCompleted::class, $event);
        self::assertSame(RuntimeOperation::Run, $event->operation());
        self::assertSame($application, $event->application());
        self::assertSame($result, $event->result());
    }

    public function testBootAndShutdownAreRepresentedByTheirExactOperations(): void
    {
        $application = Framework::create();
        $observer = new RecordingEventObserver();
        $kernel = new ObservedKernel($application->kernel(), $observer);

        self::assertTrue($kernel->boot($application)->succeeded());
        self::assertTrue($kernel->shutdown($application)->succeeded());

        self::assertCount(2, $observer->events);
        self::assertSame(
            RuntimeOperation::Boot,
            self::completedEvent($observer->events[0])->operation(),
        );
        self::assertSame(
            RuntimeOperation::Shutdown,
            self::completedEvent($observer->events[1])->operation(),
        );
    }

    public function testObserverExceptionCannotAlterSuccessfulDelegateResult(): void
    {
        $application = Framework::create();
        $observer = new RecordingEventObserver(new \RuntimeException('observer exploded'));
        $kernel = new ObservedKernel($application->kernel(), $observer);

        $result = $kernel->run($application);

        self::assertTrue($result->succeeded());
        self::assertTrue($application->runtime()->isRunning());
        self::assertNull($application->runtime()->failure());
        self::assertCount(1, $observer->events);
    }

    public function testFailedBootResultAndOriginalCauseRemainUnchanged(): void
    {
        $application = Framework::create();
        $cause = new \RuntimeException('delegate failure');
        $delegate = self::kernelReturningFailure($cause);
        $observer = new RecordingEventObserver(new \RuntimeException('observer exploded'));
        $kernel = new ObservedKernel($delegate, $observer);

        $result = $kernel->boot($application);

        self::assertTrue($result->failed());
        self::assertSame($cause, $result->cause());
        self::assertCount(1, $observer->events);
        self::assertSame($result, self::completedEvent($observer->events[0])->result());
    }

    public function testDelegateExceptionEscapesUnchangedAndIsNotObserved(): void
    {
        $application = Framework::create();
        $cause = new \RuntimeException('delegate exploded');
        $delegate = new class ($cause) implements KernelInterface {
            public function __construct(private \Throwable $cause)
            {
            }

            public function boot(ApplicationInterface $application): BootResult
            {
                throw $this->cause;
            }

            public function run(ApplicationInterface $application): BootResult
            {
                throw $this->cause;
            }

            public function shutdown(ApplicationInterface $application): BootResult
            {
                throw $this->cause;
            }
        };
        $observer = new RecordingEventObserver();
        $kernel = new ObservedKernel($delegate, $observer);

        try {
            $kernel->run($application);
            self::fail('The delegate exception was expected.');
        } catch (\Throwable $thrown) {
            self::assertSame($cause, $thrown);
        }

        self::assertSame([], $observer->events);
    }

    public function testCompletedEventSerializationContainsNoThrowableOrApplicationObject(): void
    {
        $application = Framework::create();
        $observer = new RecordingEventObserver();
        $kernel = new ObservedKernel($application->kernel(), $observer);
        $result = $kernel->run($application);
        $event = self::completedEvent($observer->events[0]);

        self::assertSame([
            'event' => 'runtime.operation.completed',
            'operation' => 'run',
            'succeeded' => true,
            'state' => 'running',
            'stage' => $result->stage()->value,
            'timestamp' => $event->timestamp()->format(DATE_ATOM),
        ], $event->jsonSerialize());
    }

    private static function completedEvent(object $event): RuntimeOperationCompleted
    {
        self::assertInstanceOf(RuntimeOperationCompleted::class, $event);

        return $event;
    }

    private static function kernelReturningFailure(\Throwable $cause): KernelInterface
    {
        return new class ($cause) implements KernelInterface {
            public function __construct(private \Throwable $cause)
            {
            }

            public function boot(ApplicationInterface $application): BootResult
            {
                return self::failure($this->cause);
            }

            public function run(ApplicationInterface $application): BootResult
            {
                return self::failure($this->cause);
            }

            public function shutdown(ApplicationInterface $application): BootResult
            {
                return self::failure($this->cause);
            }

            private static function failure(\Throwable $cause): BootResult
            {
                $now = new \DateTimeImmutable();

                return BootResult::failure(
                    \Sif\Foundation\BootStage::Failed,
                    $now,
                    $now,
                    [new \Sif\Foundation\DTO\BootError(
                        'delegate.failure',
                        $cause->getMessage(),
                        \Sif\Foundation\BootStage::Failed,
                    )],
                    $cause,
                );
            }
        };
    }
}
