<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Runtime;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Contracts\CapabilityProviderInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\MutableErrorHandlingApplicationInterface;
use Sif\Foundation\ErrorHandling\Contracts\ErrorHandlerInterface;
use Sif\Foundation\ServiceProvider;

final class RuntimeErrorHandlingServiceProvider extends ServiceProvider implements CapabilityProviderInterface
{
    public function __construct(private readonly ErrorHandlerInterface $errorHandler)
    {
    }

    public function register(ApplicationInterface $application): void
    {
        if ($application instanceof MutableErrorHandlingApplicationInterface) {
            $application->setErrorHandler($this->errorHandler);
        }
    }

    /** @return iterable<CapabilityInterface> */
    public function capabilities(): iterable
    {
        yield new NamedCapability('error-handling');
    }
}
