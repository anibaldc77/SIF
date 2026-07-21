<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Pipeline;

use Sif\Builder\Engine\BuilderPhase;
use Sif\Builder\Engine\Exception\InvalidPipelineTransitionException;

final class BuilderLifecycle
{
    /** @var array<string, list<BuilderPhase>> */
    private const TRANSITIONS = [
        'created' => [BuilderPhase::PREPARING, BuilderPhase::FAILED],
        'preparing' => [BuilderPhase::DISCOVERING, BuilderPhase::FAILED],
        'discovering' => [BuilderPhase::INDEXING, BuilderPhase::FAILED],
        'indexing' => [BuilderPhase::ANALYZING, BuilderPhase::FAILED],
        'analyzing' => [BuilderPhase::GENERATING, BuilderPhase::FINALIZING, BuilderPhase::FAILED],
        'generating' => [BuilderPhase::FINALIZING, BuilderPhase::FAILED],
        'finalizing' => [BuilderPhase::COMPLETED, BuilderPhase::FAILED],
        'completed' => [],
        'failed' => [],
    ];

    public function transition(BuilderPhase $from, BuilderPhase $to): void
    {
        foreach (self::TRANSITIONS[$from->value] as $allowed) {
            if ($allowed === $to) {
                return;
            }
        }

        throw new InvalidPipelineTransitionException(sprintf(
            'Invalid builder pipeline transition from "%s" to "%s".',
            $from->value,
            $to->value,
        ));
    }
}
