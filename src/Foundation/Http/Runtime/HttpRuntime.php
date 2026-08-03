<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Runtime;

use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Contracts\ResponseEmitterInterface;
use Sif\Foundation\Contracts\ResponseInterface;

final readonly class HttpRuntime
{
    public function __construct(private NativeHttpKernel $kernel)
    {
    }

    public function kernel(): NativeHttpKernel
    {
        return $this->kernel;
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->kernel->handle($request);
    }

    public function runNative(ResponseEmitterInterface $emitter): ResponseInterface
    {
        return $this->kernel->run($emitter);
    }

    /** @return array{native_kernel: bool, request_lifecycle: bool, response_emission: bool} */
    public function summary(): array
    {
        return [
            'native_kernel' => true,
            'request_lifecycle' => true,
            'response_emission' => true,
        ];
    }
}
