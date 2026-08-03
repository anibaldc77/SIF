<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Runtime;

use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseEmitterInterface;
use Sif\Foundation\Contracts\ResponseInterface;
use Sif\Foundation\Http\Lifecycle\HttpRequestLifecycleCoordinator;
use Sif\Foundation\Http\Transport\NativeRequestFactory;

final readonly class NativeHttpKernel
{
    public function __construct(
        private HttpRequestLifecycleCoordinator $lifecycle,
        private NativeRequestFactory $requests = new NativeRequestFactory(),
    ) {
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->lifecycle->handle($request);
    }

    public function run(ResponseEmitterInterface $emitter): ResponseInterface
    {
        $response = $this->handle($this->requests->fromGlobals());
        $emitter->emit($response);

        return $response;
    }
}
