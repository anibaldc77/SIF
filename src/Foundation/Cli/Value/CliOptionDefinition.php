<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Value;

use Sif\Foundation\Cli\Exceptions\InvalidCliDefinitionException;

final readonly class CliOptionDefinition
{
    public function __construct(
        private CliOptionName $name,
        private string $description,
        private ?string $shortcut = null,
        private bool $requiresValue = false,
        private bool $repeatable = false,
        private bool $sensitive = false,
    ) {
        if (trim($this->description) === '') {
            throw new InvalidCliDefinitionException('A CLI option description cannot be blank.');
        }

        if ($this->shortcut !== null && preg_match('/^[A-Za-z0-9]$/', $this->shortcut) !== 1) {
            throw new InvalidCliDefinitionException('A CLI option shortcut must be one alphanumeric character.');
        }

        if ($this->repeatable && !$this->requiresValue) {
            throw new InvalidCliDefinitionException('A repeatable CLI option must require a value.');
        }

        if ($this->sensitive && !$this->requiresValue) {
            throw new InvalidCliDefinitionException('A sensitive CLI option must require a value.');
        }
    }

    public function name(): CliOptionName { return $this->name; }
    public function description(): string { return $this->description; }
    public function shortcut(): ?string { return $this->shortcut; }
    public function requiresValue(): bool { return $this->requiresValue; }
    public function repeatable(): bool { return $this->repeatable; }
    public function sensitive(): bool { return $this->sensitive; }

    /** @return array{name: string, description: string, shortcut: string|null, requires_value: bool, repeatable: bool, sensitive: bool} */
    public function summary(): array
    {
        return [
            'name' => $this->name->value(),
            'description' => $this->description,
            'shortcut' => $this->shortcut,
            'requires_value' => $this->requiresValue,
            'repeatable' => $this->repeatable,
            'sensitive' => $this->sensitive,
        ];
    }
}
