<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

final readonly class ContinuousAccessExecutionResult
{
    /**
     * @param list<string> $completedActions
     * @param list<string> $failedActions
     */
    public function __construct(
        private bool $successful,
        private array $completedActions = [],
        private array $failedActions = []
    ) {
    }

    public function successful(): bool
    {
        return $this->successful;
    }

    /**
     * @return list<string>
     */
    public function completedActions(): array
    {
        return $this->completedActions;
    }

    /**
     * @return list<string>
     */
    public function failedActions(): array
    {
        return $this->failedActions;
    }
}
