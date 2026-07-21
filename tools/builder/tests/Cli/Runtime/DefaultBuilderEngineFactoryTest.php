<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Runtime;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Runtime\DefaultBuilderEngineFactory;
use Sif\Builder\Cli\Runtime\EngineExecutionMode;
use Sif\Builder\Engine\Extension\AnalyzerRegistry;
use Sif\Builder\Engine\Extension\GeneratorRegistry;
use Sif\Builder\Engine\Pipeline\BuilderEngine;

final class DefaultBuilderEngineFactoryTest extends TestCase
{
    public function testItCreatesEngineForBothExecutionModes(): void
    {
        $factory = new DefaultBuilderEngineFactory(new AnalyzerRegistry(), new GeneratorRegistry());

        self::assertInstanceOf(BuilderEngine::class, $factory->create(EngineExecutionMode::BUILD));
        self::assertInstanceOf(BuilderEngine::class, $factory->create(EngineExecutionMode::ANALYSIS_ONLY));
    }
}
