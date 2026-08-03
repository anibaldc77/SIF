<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Runtime;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Contracts\CapabilityProviderInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\MutableHttpApplicationInterface;
use Sif\Foundation\ServiceProvider;

final class HttpRuntimeServiceProvider extends ServiceProvider implements CapabilityProviderInterface
{
    public function __construct(private readonly HttpRuntime $runtime)
    {
    }

    public function register(ApplicationInterface $application): void
    {
        if ($application instanceof MutableHttpApplicationInterface) {
            $application->setHttp($this->runtime);
        }
    }

    /** @return iterable<CapabilityInterface> */
    public function capabilities(): iterable
    {
        yield new NamedCapability('http');
        yield new NamedCapability('http.lifecycle');
        yield new NamedCapability('http.native-transport');
    }
}
