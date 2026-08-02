<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Help;

use Sif\Foundation\Cli\Value\CliCommandMetadata;

final readonly class CliCommandHelp
{
    public function __construct(private CliCommandMetadata $metadata)
    {
    }

    public function metadata(): CliCommandMetadata
    {
        return $this->metadata;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->metadata->name()->value(),
            'description' => $this->metadata->description(),
            'help' => $this->metadata->help(),
            'usage' => $this->usage(),
            'arguments' => array_map(static fn ($argument): array => $argument->summary(), $this->metadata->arguments()),
            'options' => array_map(static fn ($option): array => $option->summary(), $this->metadata->options()),
            'aliases' => array_map(static fn ($alias): string => $alias->value(), $this->metadata->aliases()),
            'operational_class' => $this->metadata->operationalClass()->value(),
            'interactive_allowed' => $this->metadata->interactiveAllowed(),
            'destructive' => $this->metadata->destructive(),
        ];
    }

    public function usage(): string
    {
        $parts = ['sif', $this->metadata->name()->value()];
        foreach ($this->metadata->arguments() as $argument) {
            $name = $argument->name()->value() . ($argument->variadic() ? '...' : '');
            $parts[] = $argument->required() ? '<' . $name . '>' : '[' . $name . ']';
        }
        if ($this->metadata->options() !== []) {
            $parts[] = '[options]';
        }
        return implode(' ', $parts);
    }
}
