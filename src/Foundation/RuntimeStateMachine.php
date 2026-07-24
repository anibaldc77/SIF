<?php

declare(strict_types=1);

namespace Sif\Foundation;

use Sif\Foundation\Exceptions\InvalidRuntimeTransitionException;

/**
 * Defines and validates the Runtime lifecycle transition graph.
 *
 * This component is stateless. Runtime remains the single source of truth for
 * the current state, while Kernel remains the lifecycle transition authority.
 */
final class RuntimeStateMachine
{
    /**
     * @return list<RuntimeState>
     */
    public function allowedTransitions(RuntimeState $from): array
    {
        return match ($from) {
            RuntimeState::Created => [
                RuntimeState::Bootstrapping,
                RuntimeState::Failed,
            ],
            RuntimeState::Bootstrapping => [
                RuntimeState::Booted,
                RuntimeState::Failed,
            ],
            RuntimeState::Booted => [
                RuntimeState::Running,
                RuntimeState::Stopping,
                RuntimeState::Failed,
            ],
            RuntimeState::Running => [
                RuntimeState::Stopping,
                RuntimeState::Failed,
            ],
            RuntimeState::Stopping => [
                RuntimeState::Stopped,
                RuntimeState::Failed,
            ],
            RuntimeState::Stopped, RuntimeState::Failed => [],
        };
    }

    public function canTransition(
        RuntimeState $from,
        RuntimeState $to,
    ): bool {
        return in_array($to, $this->allowedTransitions($from), true);
    }

    /**
     * @throws InvalidRuntimeTransitionException
     */
    public function assertCanTransition(
        RuntimeState $from,
        RuntimeState $to,
    ): void {
        if (!$this->canTransition($from, $to)) {
            throw InvalidRuntimeTransitionException::between($from, $to);
        }
    }
}
