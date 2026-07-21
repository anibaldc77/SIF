<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Pipeline;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Exception\InvalidPipelineTransitionException;
use Sif\Builder\Engine\Pipeline\BuilderLifecycle;

final class BuilderLifecycleTest extends TestCase
{
    public function testAcceptsApprovedLifecycleAndAnalyzerToFinalizeShortcut(): void
    {
        $lifecycle = new BuilderLifecycle();

        $lifecycle->transition(BuilderPhase::CREATED, BuilderPhase::PREPARING);
        $lifecycle->transition(BuilderPhase::PREPARING, BuilderPhase::DISCOVERING);
        $lifecycle->transition(BuilderPhase::DISCOVERING, BuilderPhase::INDEXING);
        $lifecycle->transition(BuilderPhase::INDEXING, BuilderPhase::ANALYZING);
        $lifecycle->transition(BuilderPhase::ANALYZING, BuilderPhase::FINALIZING);
        $lifecycle->transition(BuilderPhase::FINALIZING, BuilderPhase::COMPLETED);

        self::addToAssertionCount(6);
    }

    public function testRejectsInvalidTransition(): void
    {
        $this->expectException(InvalidPipelineTransitionException::class);

        (new BuilderLifecycle())->transition(BuilderPhase::CREATED, BuilderPhase::GENERATING);
    }
}
