<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Configuration;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Configuration\BuilderRequestFactory;
use Sif\Builder\Cli\Configuration\WorkingDirectoryPathResolver;
use Sif\Builder\Cli\Exception\RequestMappingException;
use Sif\Builder\Cli\Input\CommandInput;
use Sif\Builder\Cli\Runtime\EngineExecutionMode;
use Sif\Builder\Engine\ExecutionPolicy;

final class BuilderRequestFactoryTest extends TestCase
{
    public function testItMapsBuildInputDeterministically(): void
    {
        $factory = new BuilderRequestFactory(new WorkingDirectoryPathResolver('D:/SIF'));
        $input = new CommandInput('build', ['repository'], [
            'output' => ['generated'],
            'analyzer' => ['reference.broken'],
            'generator' => ['repository.index'],
            'policy' => ['lenient'],
        ]);

        $request = $factory->create($input, EngineExecutionMode::BUILD);

        self::assertSame('D:/SIF/repository', $request->repositoryRoot);
        self::assertSame('D:/SIF/generated', $request->outputRoot);
        self::assertSame(ExecutionPolicy::LENIENT, $request->policy);
        self::assertSame(['reference.broken'], $request->enabledAnalyzers);
        self::assertSame(['repository.index'], $request->enabledGenerators);
    }

    public function testAnalysisOnlyRemovesOutputAndGeneratorSelection(): void
    {
        $factory = new BuilderRequestFactory(new WorkingDirectoryPathResolver('/workspace'));
        $input = new CommandInput('validate', [], [
            'output' => ['generated'],
            'generator' => ['repository.index'],
        ]);

        $request = $factory->create($input, EngineExecutionMode::ANALYSIS_ONLY);

        self::assertSame('/workspace', $request->repositoryRoot);
        self::assertNull($request->outputRoot);
        self::assertSame([], $request->enabledGenerators);
    }

    public function testItRejectsUnsupportedPolicies(): void
    {
        $factory = new BuilderRequestFactory(new WorkingDirectoryPathResolver('/workspace'));

        $this->expectException(RequestMappingException::class);
        $factory->create(
            new CommandInput('build', [], ['policy' => ['unsafe']]),
            EngineExecutionMode::BUILD,
        );
    }
}
