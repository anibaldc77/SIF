<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Action;

use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;

final readonly class ControllerActionRequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private string $actionIdentifier,
        private ControllerActionDispatcher $dispatcher,
    ) {
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        $context = $request->attributes()->get('execution.context');

        return $this->dispatcher->dispatch(
            $this->actionIdentifier,
            $request,
            $context instanceof ExecutionContextInterface ? $context : null,
        );
    }
}
