<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

final readonly class CompiledContainerDefinition
{
    /**
     * @var list<CompiledServiceDefinition>
     */
    private array $services;

    /**
     * @param list<CompiledServiceDefinition> $services
     */
    public function __construct(
        array $services,
        private string $fingerprint,
    ) {
        $this->services = array_values($services);
    }

    /**
     * @return list<CompiledServiceDefinition>
     */
    public function services(): array
    {
        return $this->services;
    }

    public function fingerprint(): string
    {
        return $this->fingerprint;
    }

    /**
     * @return array{
     *   fingerprint:string,
     *   services:list<array{
     *     identifier:string,
     *     kind:string,
     *     lifetime:?string,
     *     class:?string,
     *     alias:?string,
     *     autowire:bool,
     *     bindings:array<string, array{kind:string,value:mixed,service:?string}>,
     *     tags:list<array{name:string,priority:int,metadata:array<string,scalar|null>}>,
     *     registration_order:int
     *   }>
     * }
     */
    public function toArray(): array
    {
        return [
            'fingerprint' => $this->fingerprint,
            'services' => array_map(
                static fn (
                    CompiledServiceDefinition $definition,
                ): array => $definition->toArray(),
                $this->services,
            ),
        ];
    }
}
