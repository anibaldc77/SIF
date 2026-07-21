<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Input;

use Sif\Builder\Cli\Contract\ArgumentParserInterface;
use Sif\Builder\Cli\Exception\ArgumentParsingException;

final class ArgumentParser implements ArgumentParserInterface
{
    /** @var array<string, true> */
    private const VALUE_OPTIONS = [
        'repository' => true,
        'output' => true,
        'policy' => true,
        'analyzer' => true,
        'generator' => true,
        'format' => true,
    ];

    /** @var array<string, true> */
    private const FLAGS = [
        'no-write' => true,
        'quiet' => true,
        'verbose' => true,
        'help' => true,
        'version' => true,
        'strict' => true,
        'lenient' => true,
    ];

    public function parse(ArgvInput $input): CommandInput
    {
        $tokens = $input->tokens;
        $commandName = array_shift($tokens);

        if ($commandName === null || trim($commandName) === '') {
            throw new ArgumentParsingException('A command name is required.');
        }
        if (str_starts_with($commandName, '-')) {
            throw new ArgumentParsingException('The command name must appear before options.');
        }

        $arguments = [];
        $options = [];
        $flags = [];
        $endOfOptions = false;

        for ($index = 0, $count = count($tokens); $index < $count; $index++) {
            $token = $tokens[$index];

            if ($endOfOptions) {
                $arguments[] = $token;
                continue;
            }

            if ($token === '--') {
                $endOfOptions = true;
                continue;
            }

            if (!str_starts_with($token, '--')) {
                if (str_starts_with($token, '-') && $token !== '-') {
                    throw new ArgumentParsingException(sprintf('Short options are not supported: "%s".', $token));
                }

                $arguments[] = $token;
                continue;
            }

            [$name, $inlineValue, $hasInlineValue] = $this->splitLongOption($token);

            if (isset(self::FLAGS[$name])) {
                if ($hasInlineValue) {
                    throw new ArgumentParsingException(sprintf('Flag "--%s" does not accept a value.', $name));
                }
                if (in_array($name, $flags, true)) {
                    throw new ArgumentParsingException(sprintf('Flag "--%s" cannot be repeated.', $name));
                }

                $flags[] = $name;
                continue;
            }

            if (!isset(self::VALUE_OPTIONS[$name])) {
                throw new ArgumentParsingException(sprintf('Unknown option "--%s".', $name));
            }

            $value = $inlineValue;
            if (!$hasInlineValue) {
                $index++;
                if ($index >= $count || $tokens[$index] === '--' || str_starts_with($tokens[$index], '--')) {
                    throw new ArgumentParsingException(sprintf('Option "--%s" requires a value.', $name));
                }
                $value = $tokens[$index];
            }

            if ($value === '') {
                throw new ArgumentParsingException(sprintf('Option "--%s" requires a non-empty value.', $name));
            }

            $options[$name] ??= [];
            $options[$name][] = $value;
        }

        $this->validateMutualExclusions($flags);

        return new CommandInput($commandName, $arguments, $options, $flags);
    }

    /** @return array{0: string, 1: string, 2: bool} */
    private function splitLongOption(string $token): array
    {
        $raw = substr($token, 2);
        if ($raw === '') {
            throw new ArgumentParsingException('An empty long option is invalid.');
        }

        $separator = strpos($raw, '=');
        if ($separator === false) {
            return [$this->normalizeOptionName($raw), '', false];
        }

        return [
            $this->normalizeOptionName(substr($raw, 0, $separator)),
            substr($raw, $separator + 1),
            true,
        ];
    }

    private function normalizeOptionName(string $name): string
    {
        $name = strtolower(trim($name));
        if (!preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $name)) {
            throw new ArgumentParsingException(sprintf('Option name "%s" is invalid.', $name));
        }

        return $name;
    }

    /** @param list<string> $flags */
    private function validateMutualExclusions(array $flags): void
    {
        if (in_array('quiet', $flags, true) && in_array('verbose', $flags, true)) {
            throw new ArgumentParsingException('Flags "--quiet" and "--verbose" cannot be combined.');
        }
        if (in_array('strict', $flags, true) && in_array('lenient', $flags, true)) {
            throw new ArgumentParsingException('Flags "--strict" and "--lenient" cannot be combined.');
        }
    }
}
