<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

final readonly class ContextualBinding
{
    public function __construct(
        private ServiceIdentifier $consumer,
        private string $parameterName,
        private ConstructorArgumentBinding $binding,
    ) {
        if (trim($this->parameterName) === '') {
            throw new \Sif\Foundation\Exceptions\InvalidContextualBindingException(
                'Contextual binding parameter name cannot be empty.',
            );
        }
    }

    public function consumer(): ServiceIdentifier
    {
        return $this->consumer;
    }

    public function parameterName(): string
    {
        return $this->parameterName;
    }

    public function binding(): ConstructorArgumentBinding
    {
        return $this->binding;
    }

    public function key(): string
    {
        return $this->consumer->value() . '::' . $this->parameterName;
    }
}
