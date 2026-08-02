<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Input;

use Sif\Foundation\Cli\Contracts\CliInputInterface;
use Sif\Foundation\Cli\Exceptions\CliConsoleException;

final readonly class ArrayCliInput implements CliInputInterface
{
    /** @var list<string> */
    private array $tokens;

    /** @var array<string, string> */
    private array $environment;

    /**
     * @param list<string> $tokens
     * @param array<string, string> $environment
     */
    public function __construct(array $tokens, array $environment = [])
    {
        foreach ($tokens as $token) {
            if (str_contains($token, "\0")) {
                throw new CliConsoleException('CLI process tokens cannot contain null bytes.');
            }
        }

        foreach ($environment as $name => $value) {
            if (preg_match('/^[A-Z_][A-Z0-9_]*$/', $name) !== 1 || str_contains($value, "\0")) {
                throw new CliConsoleException(sprintf('Invalid CLI process environment entry "%s".', $name));
            }
        }

        $normalizedEnvironment = $environment;
        ksort($normalizedEnvironment);

        $this->tokens = array_values($tokens);
        $this->environment = $normalizedEnvironment;
    }

    /** @return list<string> */
    public function tokens(): array
    {
        return $this->tokens;
    }

    /** @return array<string, string> */
    public function environment(): array
    {
        return $this->environment;
    }
}
