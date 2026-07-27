<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

final readonly class ContainerComposition
{
    public function __construct(
        private ServiceDefinitionRegistry $definitions,
        private ContextualBindingRegistry $contextualBindings,
        private DefinitionServiceContainer $container,
        private ContainerDefinitionValidator $validator,
        private ContainerDefinitionCompiler $compiler,
    ) {
    }

    public function definitions(): ServiceDefinitionRegistry
    {
        return $this->definitions;
    }

    public function contextualBindings(): ContextualBindingRegistry
    {
        return $this->contextualBindings;
    }

    public function container(): DefinitionServiceContainer
    {
        return $this->container;
    }

    public function compatibility(): StringServiceContainerAdapter
    {
        return new StringServiceContainerAdapter($this->container);
    }

    public function validator(): ContainerDefinitionValidator
    {
        return $this->validator;
    }

    public function compiler(): ContainerDefinitionCompiler
    {
        return $this->compiler;
    }
}
