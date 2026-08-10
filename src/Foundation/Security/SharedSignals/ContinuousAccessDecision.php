<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

final readonly class ContinuousAccessDecision
{
    /**
     * @param list<ContinuousAccessAction> $actions
     * @param list<string> $reasons
     */
    public function __construct(
        private array $actions,
        private array $reasons = []
    ) {
    }

    /**
     * @return list<ContinuousAccessAction>
     */
    public function actions(): array
    {
        return $this->actions;
    }

    /**
     * @return list<string>
     */
    public function reasons(): array
    {
        return $this->reasons;
    }

    public function requiresAction(): bool
    {
        foreach ($this->actions as $action) {
            if ($action->value() !== ContinuousAccessAction::NONE) {
                return true;
            }
        }

        return false;
    }
}
