<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Middleware;

use Sif\Foundation\Contracts\HttpMiddlewareInterface;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;

final readonly class MiddlewarePipeline implements RequestHandlerInterface
{
    /** @var list<HttpMiddlewareInterface> */
    private array $middleware;

    /** @param list<HttpMiddlewareInterface> $middleware */
    public function __construct(array $middleware, private RequestHandlerInterface $terminalHandler)
    {
        $this->middleware = array_values($middleware);
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->invoke(0, $request);
    }

    private function invoke(int $index, RequestInterface $request): ResponseInterface
    {
        if (!isset($this->middleware[$index])) {
            return $this->terminalHandler->handle($request);
        }

        $middleware = $this->middleware[$index];
        $next = new NextRequestHandler(fn (RequestInterface $nextRequest): ResponseInterface => $this->invoke($index + 1, $nextRequest));

        return $middleware->process($request, $next);
    }
}
