<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Cli;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Cli\Console\CliConsoleKernel;
use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Input\ArrayCliInput;
use Sif\Foundation\Cli\Output\BufferedCliOutput;
use Sif\Foundation\Cli\Parsing\CliInvocationParser;
use Sif\Foundation\Cli\Registry\CliCommandRegistry;
use Sif\Foundation\Cli\Rendering\CliCommandResultRenderer;
use Sif\Foundation\Cli\Rendering\CliOutputFormat;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;

final class CliConsoleKernelTest extends TestCase
{
    public function testKernelParsesExecutesRendersAndReturnsExitCode(): void
    {
        $registry = new CliCommandRegistry();
        $registry->register(new ConsoleTestCommand(false));
        $kernel = $this->kernel($registry, CliOutputFormat::text());
        $output = new BufferedCliOutput();

        $exit = $kernel->run(new ArrayCliInput(['runtime:about']), $output);

        self::assertSame(0, $exit);
        self::assertSame(
            'SIF runtime' . PHP_EOL
            . '{"version":"2.x"}' . PHP_EOL,
            $output->standard(),
        );
        self::assertSame('', $output->error());
    }

    public function testParserFailuresUseGovernedInvalidUsageCodeAndErrorChannel(): void
    {
        $registry = new CliCommandRegistry();
        $kernel = $this->kernel($registry, CliOutputFormat::text());
        $output = new BufferedCliOutput();

        $exit = $kernel->run(new ArrayCliInput([]), $output);

        self::assertSame(2, $exit);
        self::assertStringContainsString('required', $output->error());
        self::assertSame('', $output->standard());
    }

    public function testExecutionFailuresAreTranslatedWithoutChangingKernelControlFlow(): void
    {
        $registry = new CliCommandRegistry();
        $registry->register(new ConsoleTestCommand(true));
        $kernel = $this->kernel($registry, CliOutputFormat::json());
        $output = new BufferedCliOutput();

        $exit = $kernel->run(new ArrayCliInput(['runtime:about']), $output);

        self::assertSame(1, $exit);
        self::assertStringContainsString('execution-failure', $output->error());
        self::assertStringContainsString('Command execution failed', $output->error());
    }

    private function kernel(CliCommandRegistry $registry, CliOutputFormat $format): CliConsoleKernel
    {
        return new CliConsoleKernel(
            $registry,
            new CliInvocationParser($registry),
            new CliCommandResultRenderer($format),
        );
    }
}

final readonly class ConsoleTestCommand implements CliCommandInterface
{
    public function __construct(private bool $fail)
    {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('runtime:about'),
            'Describes the runtime.',
            null,
            [],
            [],
            new CliOperationalClass('inspection'),
            true,
            false,
        );
    }

    public function execute(CliInvocation $invocation): CliCommandResult
    {
        if ($this->fail) {
            throw new RuntimeException('synthetic failure');
        }

        return new CliCommandResult(
            CliExitCode::success(),
            'SIF runtime',
            ['version' => '2.x'],
        );
    }
}
