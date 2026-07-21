<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Input;

use Sif\Builder\Cli\Exception\InvalidCommandInputException;

final readonly class CommandInput
{
    public string $commandName;

    /** @var list<string> */
    public array $arguments;

    /** @var array<string, list<string>> */
    public array $options;

    /** @var list<string> */
    public array $flags;

    /**
     * @param list<string> $arguments
     * @param array<string, list<string>> $options
     * @param list<string> $flags
     */
    public function __construct(
        string $commandName,
        array $arguments = [],
        array $options = [],
        array $flags = [],
    ) {
        $this->commandName = self::normalizeName($commandName, 'Command name');
        $this->arguments = self::normalizeArguments($arguments);
        $this->options = self::normalizeOptions($options);
        $this->flags = self::normalizeFlags($flags, array_keys($this->options));
    }

    public function argument(int $position): ?string
    {
        return $this->arguments[$position] ?? null;
    }

    /** @return list<string> */
    public function optionValues(string $name): array
    {
        return $this->options[self::normalizeName($name, 'Option name')] ?? [];
    }

    public function option(string $name): ?string
    {
        $values = $this->optionValues($name);

        return $values === [] ? null : $values[count($values) - 1];
    }

    public function hasOption(string $name): bool
    {
        return array_key_exists(self::normalizeName($name, 'Option name'), $this->options);
    }

    public function hasFlag(string $name): bool
    {
        return in_array(self::normalizeName($name, 'Flag name'), $this->flags, true);
    }

    /** @param list<string> $arguments @return list<string> */
    private static function normalizeArguments(array $arguments): array
    {
        $normalized = [];

        foreach ($arguments as $argument) {
            if (!is_string($argument)) {
                throw new InvalidCommandInputException('Command arguments must contain only strings.');
            }

            if (str_contains($argument, "\0")) {
                throw new InvalidCommandInputException('Command arguments must not contain null bytes.');
            }

            $normalized[] = $argument;
        }

        return $normalized;
    }

    /**
     * @param array<string, list<string>> $options
     * @return array<string, list<string>>
     */
    private static function normalizeOptions(array $options): array
    {
        $normalized = [];

        foreach ($options as $name => $values) {
            if (!is_string($name)) {
                throw new InvalidCommandInputException('Option names must be strings.');
            }

            $name = self::normalizeName($name, 'Option name');

            if (!is_array($values) || !array_is_list($values)) {
                throw new InvalidCommandInputException(sprintf('Option "%s" values must be a list of strings.', $name));
            }

            $normalizedValues = [];
            foreach ($values as $value) {
                if (!is_string($value)) {
                    throw new InvalidCommandInputException(sprintf('Option "%s" values must contain only strings.', $name));
                }
                if (str_contains($value, "\0")) {
                    throw new InvalidCommandInputException(sprintf('Option "%s" values must not contain null bytes.', $name));
                }
                $normalizedValues[] = $value;
            }

            if ($normalizedValues === []) {
                throw new InvalidCommandInputException(sprintf('Option "%s" must contain at least one value.', $name));
            }
            if (isset($normalized[$name])) {
                throw new InvalidCommandInputException(sprintf('Duplicate option "%s" after normalization.', $name));
            }

            $normalized[$name] = $normalizedValues;
        }

        return $normalized;
    }

    /** @param list<string> $flags @param list<string> $optionNames @return list<string> */
    private static function normalizeFlags(array $flags, array $optionNames): array
    {
        $normalized = [];
        $optionSet = array_fill_keys($optionNames, true);

        foreach ($flags as $flag) {
            if (!is_string($flag)) {
                throw new InvalidCommandInputException('Flags must contain only strings.');
            }

            $flag = self::normalizeName($flag, 'Flag name');
            if (isset($normalized[$flag])) {
                throw new InvalidCommandInputException(sprintf('Duplicate flag "%s".', $flag));
            }
            if (isset($optionSet[$flag])) {
                throw new InvalidCommandInputException(sprintf('Input name "%s" cannot be both an option and a flag.', $flag));
            }

            $normalized[$flag] = $flag;
        }

        return array_values($normalized);
    }

    private static function normalizeName(string $name, string $label): string
    {
        $name = strtolower(trim($name));
        $name = ltrim($name, '-');

        if (!preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $name)) {
            throw new InvalidCommandInputException(sprintf('%s "%s" is invalid.', $label, $name));
        }

        return $name;
    }
}
