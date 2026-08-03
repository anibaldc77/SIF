<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Middleware;

use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Exceptions\InvalidMiddlewarePipelineException;

final class NextRequestHandler implements RequestHandlerInterface
{
    private bool $called = false;

    /** @param \Closure(RequestInterface): ResponseInterface $next */
    public function __construct(private readonly \Closure $next)
    {
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        if ($this->called) {
            throw new InvalidMiddlewarePipelineException('Middleware attempted to invoke the next handler more than once.');
        }
        $this->called = true;
        return ($this->next)($request);
    }
}
