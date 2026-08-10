<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

final readonly class RiscEvaluationResult
{
    /**
     * @param list<string> $reasons
     */
    public function __construct(
        private RiscAccountReaction $reaction,
        private array $reasons = []
    ) {
    }

    public function reaction(): RiscAccountReaction
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
