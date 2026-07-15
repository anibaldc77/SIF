<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\BootStage;
use Sif\Foundation\Framework;
use Sif\Foundation\Tests\Fixtures\FirstRecordingProvider;
use Sif\Foundation\Tests\Fixtures\OperationLog;
use Sif\Foundation\Tests\Fixtures\SecondRecordingProvider;
use Sif\Foundation\Tests\Fixtures\ThirdRecordingProvider;

final class ServiceProviderLifecycleTest extends TestCase
{
    public function testRegisterBootAndShutdownUseApprovedOrder(): void
    {
        $log = new OperationLog();
        $application = Framework::create();
        $application->providers()->add(new FirstRecordingProvider($log, 'first'));
        $application->providers()->add(new SecondRecordingProvider($log, 'second'));

        self::assertTrue($application->run()->succeeded());
        self::assertSame([
            'first.register',
            'second.register',
            'first.boot',
            'second.boot',
        ], $log->entries);

        self::assertTrue($application->shutdown()->succeeded());
        self::assertSame([
            'first.register',
            'second.register',
            'first.boot',
            'second.boot',
            'second.shutdown',
            'first.shutdown',
        ], $log->entries);
    }

    public function testRegisterFailureStopsPhaseAndFailsRuntime(): void
    {
        $log = new OperationLog();
        $cause = new \RuntimeException('register exploded');
        $application = Framework::create();
        $application->providers()->add(new FirstRecordingProvider($log, 'first', 'register', $cause));
        $application->providers()->add(new SecondRecordingProvider($log, 'second'));

        $result = $application->run();

        self::assertTrue($result->failed());
        self::assertSame(['first.register'], $log->entries);
        self::assertTrue($application->runtime()->hasFailed());
        self::assertSame($cause, $result->cause());
        self::assertSame($cause, $application->runtime()->failure());
        self::assertSame('provider.register_failed', $result->errors()[0]->code());
        self::assertSame(BootStage::Providers, $result->errors()[0]->stage());
    }

    public function testBootFailureStopsPhaseAndFailsRuntime(): void
    {
        $log = new OperationLog();
        $cause = new \RuntimeException('boot exploded');
        $application = Framework::create();
        $application->providers()->add(new FirstRecordingProvider($log, 'first', 'boot', $cause));
        $application->providers()->add(new SecondRecordingProvider($log, 'second'));

        $result = $application->run();

        self::assertTrue($result->failed());
        self::assertSame(['first.register', 'second.register', 'first.boot'], $log->entries);
        self::assertTrue($application->runtime()->hasFailed());
        self::assertSame($cause, $result->cause());
        self::assertSame($cause, $application->runtime()->failure());
        self::assertSame('provider.boot_failed', $result->errors()[0]->code());
    }

    public function testShutdownCollectsAllFailuresAndPreservesFirstCause(): void
    {
        $log = new OperationLog();
        $firstCause = new \RuntimeException('third shutdown exploded');
        $secondCause = new \RuntimeException('second shutdown exploded');
        $application = Framework::create();
        $application->providers()->add(new FirstRecordingProvider($log, 'first'));
        $application->providers()->add(new SecondRecordingProvider($log, 'second', 'shutdown', $secondCause));
        $application->providers()->add(new ThirdRecordingProvider($log, 'third', 'shutdown', $firstCause));
        self::assertTrue($application->run()->succeeded());

        $result = $application->shutdown();

        self::assertTrue($result->failed());
        self::assertSame([
            'first.register', 'second.register', 'third.register',
            'first.boot', 'second.boot', 'third.boot',
            'third.shutdown', 'second.shutdown', 'first.shutdown',
        ], $log->entries);
        self::assertCount(2, $result->errors());
        self::assertSame($firstCause, $result->cause());
        self::assertSame($firstCause, $application->runtime()->failure());
        self::assertTrue($application->runtime()->hasFailed());
        self::assertSame('provider.shutdown_failed', $result->errors()[0]->code());
        self::assertSame('provider.shutdown_failed', $result->errors()[1]->code());
    }
}
