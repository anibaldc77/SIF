<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface HttpMiddlewareResolverInterface
{
    public function resolve(string $identifier): HttpMiddlewareInterface;
}
