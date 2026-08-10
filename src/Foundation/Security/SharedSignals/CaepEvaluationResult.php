<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

final readonly class CaepEvaluationResult
{
    /**
     * @param list<string> $reasons
     */
    public function __construct(
        private CaepAccessReaction $reaction,
        private array $reasons = []
    ) {
    }

    public function reaction(): CaepAccessReaction
    {
        return $this->reaction;
    }

    /**
     * @return list<string>
     */
    public function reasons(): array
    {
        return $this->reasons;
    }
}
