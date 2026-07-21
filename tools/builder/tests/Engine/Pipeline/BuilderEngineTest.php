<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Pipeline;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\BuilderRequest;
use Sif\Builder\Engine\BuilderStatus;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\ExecutionPolicy;
use Sif\Builder\Engine\Extension\AnalyzerRegistry;
use Sif\Builder\Engine\Extension\GeneratorRegistry;
use Sif\Builder\Engine\Pipeline\BuilderEngine;
use Sif\Builder\Tests\Engine\Pipeline\Fixtures\FixedRunIdentifierProvider;
use Sif\Builder\Tests\Engine\Pipeline\Fixtures\OperationLog;
use Sif\Builder\Tests\Engine\Pipeline\Fixtures\RecordingAnalyzer;
use Sif\Builder\Tests\Engine\Pipeline\Fixtures\RecordingGenerator;

final class BuilderEngineTest extends TestCase
{
    public function testRunsFixedPipelineAndExtensionsInRegistrationOrder(): void
    {
        $operations = new OperationLog();
        $analyzers = new AnalyzerRegistry();
        $generators = new GeneratorRegistry();
        $analyzers->register(new RecordingAnalyzer('first.analyzer', $operations));
        $analyzers->register(new RecordingAnalyzer('second.analyzer', $operations));
        $generators->register(new RecordingGenerator('first.generator', $operations));
        $generators->register(new RecordingGenerator('second.generator', $operations));

        $result = $this->engine($analyzers, $generators)->run(new BuilderRequest('/repo'));

        self::assertSame(BuilderStatus::SUCCEEDED, $result->status);
        self::assertSame([
            BuilderPhase::PREPARING,
            BuilderPhase::DISCOVERING,
            BuilderPhase::INDEXING,
            BuilderPhase::ANALYZING,
            BuilderPhase::GENERATING,
            BuilderPhase::FINALIZING,
            BuilderPhase::COMPLETED,
        ], $result->completedPhases);
        self::assertSame([
            'analyzer:first.analyzer:analyzing',
            'analyzer:second.analyzer:analyzing',
            'generator:first.generator:generating',
            'generator:second.generator:generating',
        ], $operations->all());
        self::assertTrue($analyzers->isFrozen());
        self::assertTrue($generators->isFrozen());
    }

    public function testStrictPolicyCollectsAllAnalyzerErrorsAndSkipsGenerators(): void
    {
        $operations = new OperationLog();
        $analyzers = new AnalyzerRegistry();
        $generators = new GeneratorRegistry();
        $analyzers->register(new RecordingAnalyzer('first.analyzer', $operations, DiagnosticSeverity::ERROR));
        $analyzers->register(new RecordingAnalyzer('second.analyzer', $operations, DiagnosticSeverity::ERROR));
        $generators->register(new RecordingGenerator('docs.generator', $operations));

        $result = $this->engine($analyzers, $generators)->run(new BuilderRequest(
            repositoryRoot: '/repo',
            policy: ExecutionPolicy::STRICT,
        ));

        self::assertSame(BuilderStatus::FAILED, $result->status);
        self::assertCount(2, $result->diagnostics);
        self::assertSame([
            'analyzer:first.analyzer:analyzing',
            'analyzer:second.analyzer:analyzing',
        ], $operations->all());
        self::assertNotContains(BuilderPhase::GENERATING, $result->completedPhases);
        self::assertNull($result->cause());
    }

    public function testLenientPolicyRunsGeneratorsAfterAnalyzerErrors(): void
    {
        $operations = new OperationLog();
        $analyzers = new AnalyzerRegistry();
        $generators = new GeneratorRegistry();
        $analyzers->register(new RecordingAnalyzer('quality.analyzer', $operations, DiagnosticSeverity::ERROR));
        $generators->register(new RecordingGenerator('docs.generator', $operations));

        $result = $this->engine($analyzers, $generators)->run(new BuilderRequest(
            repositoryRoot: '/repo',
            policy: ExecutionPolicy::LENIENT,
        ));

        self::assertSame(BuilderStatus::SUCCEEDED_WITH_DIAGNOSTICS, $result->status);
        self::assertContains(BuilderPhase::GENERATING, $result->completedPhases);
        self::assertSame([
            'analyzer:quality.analyzer:analyzing',
            'generator:docs.generator:generating',
        ], $operations->all());
    }

    public function testLenientPolicySkipsGeneratorsAfterFatalDiagnostic(): void
    {
        $operations = new OperationLog();
        $analyzers = new AnalyzerRegistry();
        $generators = new GeneratorRegistry();
        $analyzers->register(new RecordingAnalyzer('fatal.analyzer', $operations, DiagnosticSeverity::FATAL));
        $generators->register(new RecordingGenerator('docs.generator', $operations));

        $result = $this->engine($analyzers, $generators)->run(new BuilderRequest(
            repositoryRoot: '/repo',
            policy: ExecutionPolicy::LENIENT,
        ));

        self::assertSame(BuilderStatus::SUCCEEDED_WITH_DIAGNOSTICS, $result->status);
        self::assertNotContains(BuilderPhase::GENERATING, $result->completedPhases);
        self::assertSame(['analyzer:fatal.analyzer:analyzing'], $operations->all());
    }

    public function testMissingRequestedExtensionProducesConfigurationDiagnostic(): void
    {
        $result = $this->engine(new AnalyzerRegistry(), new GeneratorRegistry())->run(new BuilderRequest(
            repositoryRoot: '/repo',
            policy: ExecutionPolicy::LENIENT,
            enabledAnalyzers: ['missing.analyzer'],
            enabledGenerators: ['missing.generator'],
        ));

        self::assertSame(BuilderStatus::SUCCEEDED_WITH_DIAGNOSTICS, $result->status);
        self::assertSame(['CONFIG-101', 'CONFIG-102'], array_map(
            static fn ($diagnostic): string => $diagnostic->code,
            $result->diagnostics->all(),
        ));
    }

    public function testExtensionThrowableBecomesSafeDiagnosticAndDoesNotLeakMessage(): void
    {
        $operations = new OperationLog();
        $analyzers = new AnalyzerRegistry();
        $analyzers->register(new RecordingAnalyzer('unsafe.analyzer', $operations, throws: true));

        $result = $this->engine($analyzers, new GeneratorRegistry())->run(new BuilderRequest(
            repositoryRoot: '/repo',
            policy: ExecutionPolicy::LENIENT,
        ));

        self::assertSame('ANALYZER-500', $result->diagnostics->all()[0]->code);
        self::assertStringNotContainsString('Unsafe internal detail', json_encode($result, JSON_THROW_ON_ERROR));
        self::assertNull($result->cause());
    }

    private function engine(AnalyzerRegistry $analyzers, GeneratorRegistry $generators): BuilderEngine
    {
        return new BuilderEngine(
            $analyzers,
            $generators,
            new FixedRunIdentifierProvider(),
        );
    }
}
