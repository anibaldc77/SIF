<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Cli;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Cli\Input\ArrayCliInput;
use Sif\Foundation\Cli\Output\BufferedCliOutput;
use Sif\Foundation\Cli\Runtime\DefaultCliRuntimeFactory;
use Sif\Foundation\Configuration\ConfigurationRepository;
use Sif\Foundation\Environment\EnvironmentRepository;
use Sif\Foundation\Runtime;

final class CliRuntimeIntegrationTest extends TestCase
{
    public function testDefaultRuntimePublishesSafeInspectionCommands(): void
    {
        $runtime = new Runtime(new EnvironmentRepository());
        $cli = (new DefaultCliRuntimeFactory(
            $runtime,
            new ConfigurationRepository(),
            ['runtime', 'cli'],
        ))->create();

        self::assertSame(3, $cli->commands()->count());
        self::assertSame(['command_count' => 3], $cli->summary());
        self::assertCount(3, $cli->help()->commands());
    }

    public function testRuntimeExecutesThroughProcessIndependentAdapters(): void
    {
        $runtime = new Runtime(new EnvironmentRepository());
        $cli = (new DefaultCliRuntimeFactory(
            $runtime,
            new ConfigurationRepository(),
            ['runtime', 'cli'],
        ))->create();
        $output = new BufferedCliOutput();

        $exitCode = $cli->run(
            new ArrayCliInput(['runtime:capabilities']),
            $output,
        );

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('runtime', $output->standard());
        self::assertSame('', $output->error());
    }

    public function testEntryPointsAreDeliveredSeparatelyFromBuilder(): void
    {
        $root = dirname(__DIR__, 4);

        self::assertFileExists($root . '/bin/sif');
        self::assertFileExists($root . '/bin/sif.bat');
        self::assertFileExists($root . '/bin/sif-builder');
    }
}
