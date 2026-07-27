<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Container;

use Sif\Foundation\Container\LazyServiceReference;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Contracts\LazyServiceReferenceInterface;
use Sif\Foundation\Contracts\ServiceContainerInterface;
use Sif\Foundation\Exceptions\ServiceDefinitionNotFoundException;

final class StubServiceContainer implements ServiceContainerInterface
{
    /**
     * @var array<string, object>
     */
    private array $services = [];

    public function set(
        ServiceIdentifier $identifier,
        object $service,
    ): void {
        $this->services[$identifier->value()] = $service;
    }

    public function has(ServiceIdentifier $identifier): bool
    {
        return isset($this->services[$identifier->value()]);
    }

    public function get(ServiceIdentifier $identifier): object
    {
        $value = $identifier->value();

        if (!isset($this->services[$value])) {
            throw new ServiceDefinitionNotFoundException(
                sprintf('Service "%s" is not available.', $value),
            );
        }

        return $this->services[$value];
    }

    public function lazy(
        ServiceIdentifier $identifier,
    ): LazyServiceReferenceInterface {
        return new LazyServiceReference($this, $identifier);
    }
}
