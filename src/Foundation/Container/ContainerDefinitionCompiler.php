<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

use JsonException;
use Sif\Foundation\Contracts\ContainerCompilerInterface;
use Sif\Foundation\Contracts\ServiceDefinitionRegistryInterface;
use Sif\Foundation\Exceptions\ContainerCompilationException;

final readonly class ContainerDefinitionCompiler implements
    ContainerCompilerInterface
{
    public function __construct(
        private ServiceDefinitionRegistryInterface $definitions,
        private ContextualBindingRegistry $contextualBindings,
        private ContainerDefinitionValidator $validator,
    ) {
    }

    public function compile(): CompiledContainerDefinition
    {
        $report = $this->validator->validate();

        if (!$report->isValid()) {
            throw new ContainerCompilationException(
                $report,
            );
        }

        $compiled = [];
        $order = 0;

        foreach ($this->definitions->all() as $definition) {
            $compiled[] = new CompiledServiceDefinition(
                identifier: $definition->identifier()->value(),
                kind: $definition->kind()->value,
                lifetime: $definition->lifetime()?->value,
                className: $definition->className(),
                aliasTarget: $definition->aliasTarget()?->value(),
                autowire: $definition->autowire(),
                bindings: $this->compileBindings($definition),
                tags: $this->compileTags($definition),
                registrationOrder: $order,
            );

            $order++;
        }

        $payload = [
            'services' => array_map(
                static fn (
                    CompiledServiceDefinition $definition,
                ): array => $definition->toArray(),
                $compiled,
            ),
            'contextual_bindings' => $this->compileContextualBindings(),
        ];

        try {
            $json = json_encode(
                $payload,
                JSON_THROW_ON_ERROR
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE,
            );
        } catch (JsonException $failure) {
            throw new ContainerCompilationException(
                report: $report,
                cause: $failure,
            );
        }

        return new CompiledContainerDefinition(
            services: $compiled,
            fingerprint: hash('sha256', $json),
        );
    }

    /**
     * @return array<string, array{kind:string,value:mixed,service:?string}>
     */
    private function compileBindings(
        ServiceDefinition $definition,
    ): array {
        $compiled = [];

        foreach (
            $definition->constructorBindings()->all()
            as $parameter => $binding
        ) {
            $compiled[$parameter] = [
                'kind' => $binding->kind()->value,
                'value' => $binding->boundValue(),
                'service' => $binding->serviceIdentifier()?->value(),
            ];
        }

        return $compiled;
    }

    /**
     * @return list<array{name:string,priority:int,metadata:array<string,scalar|null>}>
     */
    private function compileTags(
        ServiceDefinition $definition,
    ): array {
        return array_map(
            static fn (ServiceTag $tag): array => [
                'name' => $tag->name(),
                'priority' => $tag->priority(),
                'metadata' => $tag->metadata(),
            ],
            $definition->tags(),
        );
    }

    /**
     * @return list<array{
     *   consumer:string,
     *   parameter:string,
     *   kind:string,
     *   value:mixed,
     *   service:?string
     * }>
     */
    private function compileContextualBindings(): array
    {
        $compiled = [];

        foreach ($this->contextualBindings->all() as $contextual) {
            $binding = $contextual->binding();

            $compiled[] = [
                'consumer' => $contextual->consumer()->value(),
                'parameter' => $contextual->parameterName(),
                'kind' => $binding->kind()->value,
                'value' => $binding->boundValue(),
                'service' => $binding
                    ->serviceIdentifier()
                    ?->value(),
            ];
        }

        return $compiled;
    }
}
