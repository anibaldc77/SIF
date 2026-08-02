<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Parsing;

use Sif\Foundation\Cli\Exceptions\CliParseException;
use Sif\Foundation\Cli\Registry\CliCommandRegistry;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliInteractionMode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOptionDefinition;
use Sif\Foundation\Cli\Value\CliVerbosity;

final readonly class CliInvocationParser
{
    public function __construct(private CliCommandRegistry $registry)
    {
    }

    /**
     * @param list<string> $tokens
     * @param array<string, string> $environment
     */
    public function parse(array $tokens, array $environment = []): CliInvocation
    {
        if ($tokens === []) {
            throw new CliParseException('A CLI command name is required.');
        }

        $requested = new CliCommandName(array_shift($tokens));
        $command = $this->registry->resolve($requested);
        $metadata = $command->metadata();
        $arguments = [];
        $options = [];
        $interaction = CliInteractionMode::interactive();
        $verbosity = CliVerbosity::normal();
        $optionParsing = true;

        while ($tokens !== []) {
            $token = array_shift($tokens);
            if ($optionParsing && $token === '--') {
                $optionParsing = false;
                continue;
            }
            if ($optionParsing && $token === '--no-interaction') {
                $interaction = CliInteractionMode::nonInteractive();
                continue;
            }
            if ($optionParsing && in_array($token, ['-q', '--quiet'], true)) {
                $verbosity = new CliVerbosity('quiet');
                continue;
            }
            if ($optionParsing && in_array($token, ['-v', '--verbose'], true)) {
                $verbosity = new CliVerbosity('verbose');
                continue;
            }
            if ($optionParsing && in_array($token, ['-vv', '--debug'], true)) {
                $verbosity = new CliVerbosity('debug');
                continue;
            }
            if ($optionParsing && str_starts_with($token, '--')) {
                [$name, $inline] = $this->splitLongOption($token);
                $definition = $this->optionByName($metadata, $name);
                $value = $definition->requiresValue()
                    ? ($inline ?? $this->shiftRequiredValue($tokens, $name))
                    : true;
                $this->appendOption($options, $definition, $value);
                continue;
            }
            if ($optionParsing && str_starts_with($token, '-') && $token !== '-') {
                $shortcut = substr($token, 1);
                if (strlen($shortcut) !== 1) {
                    throw new CliParseException(sprintf('Unsupported short CLI option "%s".', $token));
                }
                $definition = $this->optionByShortcut($metadata, $shortcut);
                $value = $definition->requiresValue()
                    ? $this->shiftRequiredValue($tokens, $definition->name()->value())
                    : true;
                $this->appendOption($options, $definition, $value);
                continue;
            }
            $arguments[] = $token;
        }

        $this->validateArguments($metadata, $arguments);

        return new CliInvocation(
            $metadata->name(),
            $arguments,
            $options,
            $environment,
            $interaction,
            $verbosity,
        );
    }

    /** @return array{0: string, 1: string|null} */
    private function splitLongOption(string $token): array
    {
        $raw = substr($token, 2);
        if ($raw === '') {
            throw new CliParseException('A long CLI option name cannot be blank.');
        }
        $position = strpos($raw, '=');
        return $position === false
            ? [$raw, null]
            : [substr($raw, 0, $position), substr($raw, $position + 1)];
    }

    private function optionByName(CliCommandMetadata $metadata, string $name): CliOptionDefinition
    {
        foreach ($metadata->options() as $option) {
            if ($option->name()->value() === $name) {
                return $option;
            }
        }
        throw new CliParseException(sprintf('Unknown CLI option "--%s".', $name));
    }

    private function optionByShortcut(CliCommandMetadata $metadata, string $shortcut): CliOptionDefinition
    {
        foreach ($metadata->options() as $option) {
            if ($option->shortcut() !== null && strcasecmp($option->shortcut(), $shortcut) === 0) {
                return $option;
            }
        }
        throw new CliParseException(sprintf('Unknown CLI option "-%s".', $shortcut));
    }

    /** @param list<string> $tokens */
    private function shiftRequiredValue(array &$tokens, string $name): string
    {
        if ($tokens === [] || str_starts_with($tokens[0], '-')) {
            throw new CliParseException(sprintf('CLI option "%s" requires a value.', $name));
        }
        return array_shift($tokens);
    }

    /** @param array<string, list<string|bool>> $options */
    private function appendOption(array &$options, CliOptionDefinition $definition, string|bool $value): void
    {
        $name = $definition->name()->value();
        if (isset($options[$name]) && !$definition->repeatable()) {
            throw new CliParseException(sprintf('CLI option "%s" cannot be repeated.', $name));
        }
        $options[$name] ??= [];
        $options[$name][] = $value;
    }

    /** @param list<string> $arguments */
    private function validateArguments(CliCommandMetadata $metadata, array $arguments): void
    {
        $definitions = $metadata->arguments();
        $required = 0;
        $variadic = false;
        foreach ($definitions as $definition) {
            $required += $definition->required() ? 1 : 0;
            $variadic = $variadic || $definition->variadic();
        }
        if (count($arguments) < $required) {
            throw new CliParseException(sprintf('Command "%s" requires at least %d argument(s).', $metadata->name()->value(), $required));
        }
        if (!$variadic && count($arguments) > count($definitions)) {
            throw new CliParseException(sprintf('Command "%s" received too many arguments.', $metadata->name()->value()));
        }
    }
}
