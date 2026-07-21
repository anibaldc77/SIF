<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Input;

use Sif\Builder\Cli\Exception\InvalidArgvInputException;

final readonly class ArgvInput
{
    /** @var list<string> */
    public array $tokens;

    /** @param list<string> $tokens */
    public function __construct(array $tokens)
    {
        $normalized = [];

        foreach ($tokens as $token) {
            if (!is_string($token)) {
                throw new InvalidArgvInputException('Argument vector must contain only strings.');
            }
            if (str_contains($token, "\0")) {
                throw new InvalidArgvInputException('Argument vector must not contain null bytes.');
            }

            $normalized[] = $token;
        }

        $this->tokens = $normalized;
    }

    /** @param list<string> $argv */
    public static function fromPhpArgv(array $argv): self
    {
        if ($argv === []) {
            return new self([]);
        }

        return new self(array_values(array_slice($argv, 1)));
    }
}
