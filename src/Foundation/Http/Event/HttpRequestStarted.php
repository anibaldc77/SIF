<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Event;

use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Contracts\RequestInterface;

final readonly class HttpRequestStarted
{
    public function __construct(
        private RequestInterface $request,
        private ExecutionContextInterface $context,
    ) {
    }

    public function request(): RequestInterface { return $this->request; }
    public function context(): ExecutionContextInterface { return $this->context; }
}
