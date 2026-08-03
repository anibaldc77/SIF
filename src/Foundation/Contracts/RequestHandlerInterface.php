<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface;
}
