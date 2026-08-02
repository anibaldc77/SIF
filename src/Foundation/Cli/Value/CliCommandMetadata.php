<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Value;

use Sif\Foundation\Cli\Exceptions\InvalidCliDefinitionException;

final readonly class CliCommandMetadata
{
    /** @var list<CliArgumentDefinition> */
    private array $arguments;
    /** @var list<CliOptionDefinition> */
    private array $options;
    /** @var list<CliCommandName> */
    private array $aliases;

    /**
     * @param list<CliArgumentDefinition> $arguments
     * @param list<CliOptionDefinition> $options
     * @param list<CliCommandName> $aliases
     */
    public function __construct(
        private CliCommandName $name,
        private string $description,
        private ?string $help,
        array $arguments,
        array $options,
        private CliOperationalClass $operationalClass,
        private bool $interactiveAllowed,
        private bool $destructive,
        array $aliases = [],
    ) {
        if (trim($this->description) === '') {
            throw new InvalidCliDefinitionException('A CLI command description cannot be blank.');
        }
        if ($this->help !== null && trim($this->help) === '') {
            throw new InvalidCliDefinitionException('CLI command help cannot be blank when provided.');
        }
        if ($this->destructive && !$this->operationalClass->mutatesState()) {
            throw new InvalidCliDefinitionException('A destructive command must use a mutating operational class.');
        }

        $this->arguments = $this->validateArguments($arguments);
        $this->options = $this->validateOptions($options);
        $this->aliases = $this->validateAliases($aliases);
    }

    public function name(): CliCommandName { return $this->name; }
    public function description(): string { return $this->description; }
    public function help(): ?string { return $this->help; }
    /** @return list<CliArgumentDefinition> */ public function arguments(): array { return $this->arguments; }
    /** @return list<CliOptionDefinition> */ public function options(): array { return $this->options; }
    public function operationalClass(): CliOperationalClass { return $this->operationalClass; }
    public function interactiveAllowed(): bool { return $this->interactiveAllowed; }
    public function destructive(): bool { return $this->destructive; }
    /** @return list<CliCommandName> */ public function aliases(): array { return $this->aliases; }

    /**
     * @param list<CliArgumentDefinition> $arguments
     *
     * @return list<CliArgumentDefinition>
     */
    private function validateArguments(array $arguments): array
    {
        $seen = [];
        $optionalSeen = false;
        foreach ($arguments as $index => $argument) {
            $name = $argument->name()->value();
            if (isset($seen[$name])) {
                throw new InvalidCliDefinitionException(sprintf('Duplicate CLI argument "%s".', $name));
            }
            if ($optionalSeen && $argument->required()) {
                throw new InvalidCliDefinitionException('Required CLI arguments cannot follow optional arguments.');
            }
            if ($argument->variadic() && $index !== array_key_last($arguments)) {
                throw new InvalidCliDefinitionException('A variadic CLI argument must be the final argument.');
            }
            $optionalSeen = $optionalSeen || !$argument->required();
            $seen[$name] = true;
        }
        return array_values($arguments);
    }

    /**
     * @param list<CliOptionDefinition> $options
     *
     * @return list<CliOptionDefinition>
     */
    private function validateOptions(array $options): array
    {
        $names = [];
        $shortcuts = [];
        foreach ($options as $option) {
            $name = $option->name()->value();
            if (isset($names[$name])) {
                throw new InvalidCliDefinitionException(sprintf('Duplicate CLI option "%s".', $name));
            }
            $shortcut = $option->shortcut();
            if ($shortcut !== null && isset($shortcuts[strtolower($shortcut)])) {
                throw new InvalidCliDefinitionException(sprintf('Duplicate CLI option shortcut "%s".', $shortcut));
            }
            $names[$name] = true;
            if ($shortcut !== null) {
                $shortcuts[strtolower($shortcut)] = true;
            }
        }
        return array_values($options);
    }

    /**
     * @param list<CliCommandName> $aliases
     *
     * @return list<CliCommandName>
     */
    private function validateAliases(array $aliases): array
    {
        $seen = [$this->name->value() => true];
        foreach ($aliases as $alias) {
            $value = $alias->value();
            if (isset($seen[$value])) {
                throw new InvalidCliDefinitionException(sprintf('Duplicate CLI command alias "%s".', $value));
            }
            $seen[$value] = true;
        }
        return array_values($aliases);
    }
}
