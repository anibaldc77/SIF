<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Event;

use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Throwable;

final readonly class HttpRequestFailed
{
    public function __construct(
        private RequestInterface $request,
        private Throwable $throwable,
        private ResponseInterface $response,
        private ExecutionContextInterface $context,
    ) {
    }

    public function request(): RequestInterface { return $this->request; }
    public function throwable(): Throwable { return $this->throwable; }
    public function response(): ResponseInterface { return $this->response; }
    public function context(): ExecutionContextInterface { return $this->context; }
}
