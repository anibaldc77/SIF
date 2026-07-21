<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Command;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Command\BuildCommand;
use Sif\Builder\Cli\Command\ValidateCommand;
use Sif\Builder\Cli\Configuration\BuilderRequestFactory;
use Sif\Builder\Cli\Configuration\WorkingDirectoryPathResolver;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Input\CommandInput;
use Sif\Builder\Cli\Runtime\EngineExecutionMode;
use Sif\Builder\Tests\Cli\Fixtures\RecordingBuilderEngineFactory;

final class ExecutionCommandTest extends TestCase
{
    public function testBuildUsesBuildCompositionAndCarriesBuilderResult(): void
    {
        $engines = new RecordingBuilderEngineFactory();
        $command = new BuildCommand(
            new BuilderRequestFactory(new WorkingDirectoryPathResolver('/workspace')),
            $engines,
        );

        $result = $command->execute(new CommandInput('build', ['repo'], ['output' => ['out']]));

        self::assertSame(ExitCode::SUCCESS, $result->exitCode);
        self::assertNotNull($result->builderResult);
        self::assertSame([EngineExecutionMode::BUILD], $engines->modes);
        self::assertSame('/workspace/repo', $engines->engine->request?->repositoryRoot);
    }

    public function testValidateAndNoWriteUseAnalysisOnlyComposition(): void
    {
        $engines = new RecordingBuilderEngineFactory();
        $requests = new BuilderRequestFactory(new WorkingDirectoryPathResolver('/workspace'));

        (new ValidateCommand($requests, $engines))->execute(new CommandInput('validate'));
        (new BuildCommand($requests, $engines))->execute(new CommandInput('build', [], [], ['no-write']));

        self::assertSame(
            [EngineExecutionMode::ANALYSIS_ONLY, EngineExecutionMode::ANALYSIS_ONLY],
            $engines->modes,
        );
    }

    public function testMappingFailureDoesNotInvokeTheEngine(): void
    {
        $engines = new RecordingBuilderEngineFactory();
        $command = new BuildCommand(
            new BuilderRequestFactory(new WorkingDirectoryPathResolver('/workspace')),
            $engines,
        );

        $result = $command->execute(new CommandInput('build', ['one', 'two']));

        self::assertSame(ExitCode::INVALID_USAGE, $result->exitCode);
        self::assertSame([], $engines->modes);
    }
}
