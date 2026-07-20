<?php

declare(strict_types=1);

namespace Sif\Builder\Engine;

enum BuilderPhase: string
{
    case CREATED = 'created';
    case PREPARING = 'preparing';
    case DISCOVERING = 'discovering';
    case INDEXING = 'indexing';
    case ANALYZING = 'analyzing';
    case GENERATING = 'generating';
    case FINALIZING = 'finalizing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function isTerminal(): bool
    {
        return $this === self::COMPLETED || $this === self::FAILED;
    }
}
