<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface HttpMiddlewareInterface
{
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface;
}
