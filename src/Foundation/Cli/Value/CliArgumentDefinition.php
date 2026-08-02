<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Value;

use Sif\Foundation\Cli\Exceptions\InvalidCliDefinitionException;

final readonly class CliArgumentDefinition
{
    public function __construct(
        private CliArgumentName $name,
        private string $description,
        private bool $required = false,
        private bool $variadic = false,
        private bool $sensitive = false,
    ) {
        if (trim($this->description) === '') {
            throw new InvalidCliDefinitionException('A CLI argument description cannot be blank.');
        }

        if ($this->variadic && !$this->required) {
            throw new InvalidCliDefinitionException('A variadic CLI argument must be required.');
        }
    }

    public function name(): CliArgumentName { return $this->name; }
    public function description(): string { return $this->description; }
    public function required(): bool { return $this->required; }
    public function variadic(): bool { return $this->variadic; }
    public function sensitive(): bool { return $this->sensitive; }

    /** @return array{name: string, description: string, required: bool, variadic: bool, sensitive: bool} */
    public function summary(): array
    {
        return [
            'name' => $this->name->value(),
            'description' => $this->description,
            'required' => $this->required,
            'variadic' => $this->variadic,
            'sensitive' => $this->sensitive,
        ];
    }
}
