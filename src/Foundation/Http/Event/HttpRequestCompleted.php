<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Event;

use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseInterface;

final readonly class HttpRequestCompleted
{
    public function __construct(
        private RequestInterface $request,
        private ResponseInterface $response,
        private ExecutionContextInterface $context,
    ) {
    }

    public function request(): RequestInterface { return $this->request; }
    public function response(): ResponseInterface { return $this->response; }
    public function context(): ExecutionContextInterface { return $this->context; }
}
