<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Action;

use Sif\Foundation\Controller\Exceptions\ControllerActionException;

final class ControllerActionRegistry
{
    /** @var array<string, ControllerActionDefinition> */
    private array $actions = [];

    public function register(ControllerActionDefinition $action): void
    {
        if (isset($this->actions[$action->identifier()])) {
            throw new ControllerActionException(sprintf(
                'Controller action "%s" is already registered.',
                $action->identifier(),
            ));
        }

        $this->actions[$action->identifier()] = $action;
        ksort($this->actions);
    }

    public function has(string $identifier): bool
    {
        return isset($this->actions[$identifier]);
    }

    public function resolve(string $identifier): ControllerActionDefinition
    {
        if (!isset($this->actions[$identifier])) {
            throw new ControllerActionException(sprintf(
                'Controller action "%s" is not registered.',
                $identifier,
            ));
        }

        return $this->actions[$identifier];
    }

    /** @return list<ControllerActionDefinition> */
    public function all(): array
    {
        return array_values($this->actions);
    }

    public function count(): int
    {
        return count($this->actions);
    }
}
