<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface HttpHandlerResolverInterface
{
    public function resolve(string $identifier): RequestHandlerInterface;
}
