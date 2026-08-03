<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Dispatch;

use Sif\Foundation\Contracts\HttpHandlerResolverInterface;
use Sif\Foundation\Contracts\HttpMiddlewareInterface;
use Sif\Foundation\Contracts\HttpMiddlewareResolverInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Exceptions\HttpDispatchException;
use Sif\Foundation\Http\Middleware\MiddlewarePipeline;
use Sif\Foundation\Http\Routing\RouteMatch;

final readonly class HandlerDispatcher
{
    /** @var list<string> */
    private array $globalMiddleware;

    /** @param list<string> $globalMiddleware */
    public function __construct(
        private HttpHandlerResolverInterface $handlers,
        private HttpMiddlewareResolverInterface $middleware,
        array $globalMiddleware = [],
    ) {
        $this->globalMiddleware = $this->normalizeIdentifiers($globalMiddleware);
    }

    public function dispatch(RequestInterface $request, RouteMatch $match): ResponseInterface
    {
        if (!$match->isMatched() || $match->route() === null) {
            throw new HttpDispatchException('Only matched routes can be dispatched.');
        }

        $route = $match->route();
        $routedRequest = $request
            ->withAttribute('route.name', $route->name()->value())
            ->withAttribute('route.handler', $route->handler())
            ->withAttribute('route.parameters', $match->parameters());

        foreach ($match->parameters() as $name => $value) {
            $routedRequest = $routedRequest->withAttribute('route.parameter.' . $name, $value);
        }

        $middleware = [];
        foreach (array_merge($this->globalMiddleware, $route->middleware()) as $identifier) {
            $middleware[] = $this->middleware->resolve($identifier);
        }

        $pipeline = new MiddlewarePipeline($middleware, $this->handlers->resolve($route->handler()));

        return $pipeline->handle($routedRequest);
    }

    /**
     * @param list<string> $identifiers
     *
     * @return list<string>
     */
    private function normalizeIdentifiers(array $identifiers): array
    {
        $normalized = [];
        foreach ($identifiers as $identifier) {
            if ($identifier === '' || trim($identifier) !== $identifier) {
                throw new HttpDispatchException('Global middleware identifiers must be non-empty and trimmed.');
            }
            if (isset($normalized[$identifier])) {
                throw new HttpDispatchException(sprintf('Duplicate global middleware identifier "%s".', $identifier));
            }
            $normalized[$identifier] = $identifier;
        }
        return array_values($normalized);
    }
}
