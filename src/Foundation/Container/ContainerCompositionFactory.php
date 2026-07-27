<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

final class ContainerCompositionFactory
{
    public function create(
        ?ServiceDefinitionRegistry $definitions = null,
        ?ContextualBindingRegistry $contextualBindings = null,
    ): ContainerComposition {
        $definitions ??= new ServiceDefinitionRegistry();
        $contextualBindings ??= new ContextualBindingRegistry();

        $container = new DefinitionServiceContainer(
            definitions: $definitions,
            contextualBindings: $contextualBindings,
        );

        $validator = new ContainerDefinitionValidator(
            definitions: $definitions,
            contextualBindings: $contextualBindings,
        );

        $compiler = new ContainerDefinitionCompiler(
            definitions: $definitions,
            contextualBindings: $contextualBindings,
            validator: $validator,
        );

        return new ContainerComposition(
            definitions: $definitions,
            contextualBindings: $contextualBindings,
            container: $container,
            validator: $validator,
            compiler: $compiler,
        );
    }
}
