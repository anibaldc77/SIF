<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

final readonly class ConstructorArgumentBinding
{
    private function __construct(
        private ConstructorBindingKind $kind,
        private mixed $boundValue,
        private ?ServiceIdentifier $serviceIdentifier,
    ) {
    }

    public static function value(mixed $value): self
    {
        return new self(
            kind: ConstructorBindingKind::Value,
            boundValue: $value,
            serviceIdentifier: null,
        );
    }

    public static function service(
        ServiceIdentifier $identifier,
    ): self {
        return new self(
            kind: ConstructorBindingKind::Service,
            boundValue: null,
            serviceIdentifier: $identifier,
        );
    }

    public function kind(): ConstructorBindingKind
    {
        return $this->kind;
    }

    public function boundValue(): mixed
    {
        return $this->boundValue;
    }

    public function serviceIdentifier(): ?ServiceIdentifier
    {
        return $this->serviceIdentifier;
    }
}
