<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Resolver;

use Sif\Foundation\Contracts\ControllerResolverInterface;
use Sif\Foundation\Contracts\StringServiceContainerInterface;
use Sif\Foundation\Controller\Exceptions\ControllerActionException;

final readonly class ContainerControllerResolver implements ControllerResolverInterface
{
    public function __construct(private StringServiceContainerInterface $container)
    {
    }

    public function has(string $identifier): bool
    {
        return $this->container->has($identifier);
    }

    public function resolve(string $identifier): object
    {
        if (!$this->container->has($identifier)) {
            throw new ControllerActionException(sprintf(
                'Controller service "%s" is unavailable.',
                $identifier,
            ));
        }

        return $this->container->get($identifier);
    }
}
