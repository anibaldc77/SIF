<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Action;

use ReflectionMethod;
use Sif\Foundation\Contracts\ControllerResolverInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Controller\Api\ApiResponseFactory;
use Sif\Foundation\Controller\Api\ApiResult;
use Sif\Foundation\Controller\Argument\ActionArgumentResolver;
use Sif\Foundation\Controller\Exceptions\ControllerActionException;
use Sif\Foundation\Controller\Exceptions\ControllerArgumentResolutionException;
use Throwable;

final readonly class ControllerActionDispatcher
{
    public function __construct(
        private ControllerActionRegistry $actions,
        private ControllerResolverInterface $controllers,
        private ActionArgumentResolver $arguments,
        private ApiResponseFactory $responses = new ApiResponseFactory(),
    ) {
    }

    public function dispatch(
        string $identifier,
        RequestInterface $request,
        ?ExecutionContextInterface $context = null,
    ): ResponseInterface {
        $action = $this->actions->resolve($identifier);
        $controller = $this->controllers->resolve($action->controllerIdentifier());
        $method = $this->method($controller, $action);
        $resolution = $this->arguments->resolve($action->arguments(), $request, $context);

        if (!$resolution->successful()) {
            throw new ControllerArgumentResolutionException($resolution->issues());
        }

        if ($method->getNumberOfParameters() !== count($resolution->arguments())) {
            throw new ControllerActionException(sprintf(
                'Controller action "%s" signature does not match its registered argument definition.',
                $identifier,
            ));
        }

        try {
            $result = $method->invokeArgs($controller, $resolution->arguments());
        } catch (ControllerActionException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new ControllerActionException(sprintf(
                'Controller action "%s" invocation failed.',
                $identifier,
            ), previous: $exception);
        }

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        if ($result instanceof ApiResult) {
            return $this->responses->create($request, $result);
        }

        throw new ControllerActionException(sprintf(
            'Controller action "%s" returned an unsupported result of type "%s".',
            $identifier,
            get_debug_type($result),
        ));
    }

    private function method(object $controller, ControllerActionDefinition $action): ReflectionMethod
    {
        if (!method_exists($controller, $action->method())) {
            throw new ControllerActionException(sprintf(
                'Controller action method "%s::%s" does not exist.',
                $controller::class,
                $action->method(),
            ));
        }

        $method = new ReflectionMethod($controller, $action->method());
        if (!$method->isPublic() || $method->isStatic()) {
            throw new ControllerActionException(sprintf(
                'Controller action method "%s::%s" must be public and non-static.',
                $controller::class,
                $action->method(),
            ));
        }

        return $method;
    }
}
