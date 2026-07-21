<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Runtime;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Input\ArgvInput;
use Sif\Builder\Cli\Runtime\DefaultCliApplicationFactory;

final class DefaultCliApplicationFactoryTest extends TestCase
{
    public function testCreatesRunnableApplicationWithGovernedCommands(): void
    {
        $application = (new DefaultCliApplicationFactory(__DIR__, version: '2.0.0-test'))->create();

        $version = $application->run(new ArgvInput(['version']));
        self::assertSame(0, $version->exitCode->value);
        self::assertSame("SIF Builder 2.0.0-test\n", $version->standardOutput);

        $help = $application->run(new ArgvInput(['help']));
        self::assertSame(0, $help->exitCode->value);
        self::assertStringContainsString('build', (string) $help->standardOutput);
        self::assertStringContainsString('validate', (string) $help->standardOutput);
        self::assertStringContainsString('list', (string) $help->standardOutput);
    }

    public function testListExposesAvailableReporters(): void
    {
        $application = (new DefaultCliApplicationFactory(__DIR__))->create();
        $result = $application->run(new ArgvInput(['list']));

        self::assertSame(0, $result->exitCode->value);
        self::assertStringContainsString('report.markdown', (string) $result->standardOutput);
        self::assertStringContainsString('report.json', (string) $result->standardOutput);
    }
}
