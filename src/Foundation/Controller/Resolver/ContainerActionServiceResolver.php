<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Resolver;

use Sif\Foundation\Contracts\ActionServiceResolverInterface;
use Sif\Foundation\Contracts\StringServiceContainerInterface;
use Sif\Foundation\Controller\Exceptions\ControllerActionException;

final readonly class ContainerActionServiceResolver implements ActionServiceResolverInterface
{
    public function __construct(private StringServiceContainerInterface $container)
    {
    }

    public function has(string $identifier): bool
    {
        return $this->container->has($identifier);
    }

    public function resolve(string $identifier): mixed
    {
        if (!$this->container->has($identifier)) {
            throw new ControllerActionException(sprintf(
                'Action service "%s" is unavailable.',
                $identifier,
            ));
        }

        return $this->container->get($identifier);
    }
}
