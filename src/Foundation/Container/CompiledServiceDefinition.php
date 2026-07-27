<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

final readonly class CompiledServiceDefinition
{
    /**
     * @param list<array{name:string,priority:int,metadata:array<string,scalar|null>}> $tags
     * @param array<string, array{kind:string,value:mixed,service:?string}> $bindings
     */
    public function __construct(
        private string $identifier,
        private string $kind,
        private ?string $lifetime,
        private ?string $className,
        private ?string $aliasTarget,
        private bool $autowire,
        private array $bindings,
        private array $tags,
        private int $registrationOrder,
    ) {
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return array{
     *   identifier:string,
     *   kind:string,
     *   lifetime:?string,
     *   class:?string,
     *   alias:?string,
     *   autowire:bool,
     *   bindings:array<string, array{kind:string,value:mixed,service:?string}>,
     *   tags:list<array{name:string,priority:int,metadata:array<string,scalar|null>}>,
     *   registration_order:int
     * }
     */
    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'kind' => $this->kind,
            'lifetime' => $this->lifetime,
            'class' => $this->className,
            'alias' => $this->aliasTarget,
            'autowire' => $this->autowire,
            'bindings' => $this->bindings,
            'tags' => $this->tags,
            'registration_order' => $this->registrationOrder,
        ];
    }
}
