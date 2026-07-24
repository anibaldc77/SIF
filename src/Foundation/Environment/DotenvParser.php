<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment;

use Sif\Foundation\Environment\Contracts\EnvironmentProviderInterface;
use Sif\Foundation\Environment\Exceptions\InvalidDotenvSyntaxException;
use Sif\Foundation\Environment\Exceptions\UnresolvedEnvironmentVariableException;

final class DotenvParser
{
    public function __construct(
        private readonly ?EnvironmentProviderInterface $fallback = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function parse(string $contents): array
    {
        $values = [];
        $lines = preg_split('/\R/', $contents);

        if ($lines === false) {
            return [];
        }

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_starts_with($line, 'export ')) {
                $line = ltrim(substr($line, 7));
            }

            $separator = strpos($line, '=');

            if ($separator === false) {
                throw InvalidDotenvSyntaxException::atLine($lineNumber, 'missing assignment operator.');
            }

            $key = trim(substr($line, 0, $separator));

            if ($key === '' || preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key) !== 1) {
                throw InvalidDotenvSyntaxException::atLine($lineNumber, 'invalid variable name.');
            }

            $rawValue = ltrim(substr($line, $separator + 1));
            $values[$key] = $this->parseValue($rawValue, $values, $lineNumber);
        }

        return $values;
    }

    /**
     * @param array<string, string> $resolved
     */
    private function parseValue(string $rawValue, array $resolved, int $line): string
    {
        if ($rawValue === '') {
            return '';
        }

        if ($rawValue[0] === "'") {
            return $this->parseSingleQuoted($rawValue, $line);
        }

        if ($rawValue[0] === '"') {
            return $this->expand($this->parseDoubleQuoted($rawValue, $line), $resolved, $line);
        }

        return $this->expand($this->stripInlineComment($rawValue), $resolved, $line);
    }

    private function parseSingleQuoted(string $value, int $line): string
    {
        $length = strlen($value);

        for ($index = 1; $index < $length; $index++) {
            if ($value[$index] !== "'") {
                continue;
            }

            $remainder = trim(substr($value, $index + 1));

            if ($remainder !== '' && !str_starts_with($remainder, '#')) {
                throw InvalidDotenvSyntaxException::atLine($line, 'unexpected content after quoted value.');
            }

            return substr($value, 1, $index - 1);
        }

        throw InvalidDotenvSyntaxException::atLine($line, 'unterminated single-quoted value.');
    }

    private function parseDoubleQuoted(string $value, int $line): string
    {
        $length = strlen($value);
        $parsed = '';
        $escaped = false;

        for ($index = 1; $index < $length; $index++) {
            $character = $value[$index];

            if ($escaped) {
                $parsed .= match ($character) {
                    'n' => "\n",
                    'r' => "\r",
                    't' => "\t",
                    '"' => '"',
                    '\\' => '\\',
                    '$' => '$',
                    default => '\\' . $character,
                };
                $escaped = false;
                continue;
            }

            if ($character === '\\') {
                $escaped = true;
                continue;
            }

            if ($character !== '"') {
                $parsed .= $character;
                continue;
            }

            $remainder = trim(substr($value, $index + 1));

            if ($remainder !== '' && !str_starts_with($remainder, '#')) {
                throw InvalidDotenvSyntaxException::atLine($line, 'unexpected content after quoted value.');
            }

            return $parsed;
        }

        throw InvalidDotenvSyntaxException::atLine($line, 'unterminated double-quoted value.');
    }

    private function stripInlineComment(string $value): string
    {
        $length = strlen($value);

        for ($index = 0; $index < $length; $index++) {
            if ($value[$index] !== '#') {
                continue;
            }

            if ($index === 0 || ctype_space($value[$index - 1])) {
                return rtrim(substr($value, 0, $index));
            }
        }

        return rtrim($value);
    }

    /**
     * @param array<string, string> $resolved
     */
    private function expand(string $value, array $resolved, int $line): string
    {
        $expanded = preg_replace_callback(
            '/\$\{([A-Za-z_][A-Za-z0-9_]*)(?::-([^}]*))?\}/',
            function (array $matches) use ($resolved, $line): string {
                $key = $matches[1];

                if (array_key_exists($key, $resolved)) {
                    return $resolved[$key];
                }

                if ($this->fallback?->has($key) === true) {
                    return (string) $this->fallback->get($key);
                }

                if (array_key_exists(2, $matches)) {
                    return $matches[2];
                }

                throw UnresolvedEnvironmentVariableException::forVariable($key, $line);
            },
            $value,
        );

        if ($expanded === null) {
            throw InvalidDotenvSyntaxException::atLine($line, 'variable expansion failed.');
        }

        return $expanded;
    }
}
