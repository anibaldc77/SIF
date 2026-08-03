<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface ResponseEmitterInterface
{
    public function emit(ResponseInterface $response): void;
}
