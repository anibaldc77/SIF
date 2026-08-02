<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Input;

use Sif\Foundation\Cli\Contracts\CliInputInterface;

final readonly class NativeCliInput implements CliInputInterface
{
    private ArrayCliInput $input;

    /**
     * @param list<string> $arguments
     * @param array<string, string> $environment
     */
    public function __construct(array $arguments, array $environment = [])
    {
        $tokens = $arguments;
        if ($tokens !== []) {
            array_shift($tokens);
        }
        $this->input = new ArrayCliInput($tokens, $environment);
    }

    /** @return list<string> */
    public function tokens(): array
    {
        return $this->input->tokens();
    }

    /** @return array<string, string> */
    public function environment(): array
    {
        return $this->input->environment();
    }
}
