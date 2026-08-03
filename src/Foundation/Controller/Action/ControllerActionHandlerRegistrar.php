<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Action;

use Sif\Foundation\Http\Dispatch\HandlerRegistry;

final readonly class ControllerActionHandlerRegistrar
{
    public function __construct(
        private ControllerActionRegistry $actions,
        private ControllerActionDispatcher $dispatcher,
    ) {
    }

    public function registerInto(HandlerRegistry $handlers): void
    {
        foreach ($this->actions->all() as $action) {
            $handlers->register(
                $action->identifier(),
                new ControllerActionRequestHandler($action->identifier(), $this->dispatcher),
            );
        }
    }
}
