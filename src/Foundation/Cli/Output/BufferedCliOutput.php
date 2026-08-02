<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Output;

use Sif\Foundation\Cli\Contracts\CliOutputInterface;

final class BufferedCliOutput implements CliOutputInterface
{
    /** @var list<string> */
    private array $standard = [];

    /** @var list<string> */
    private array $errors = [];

    public function write(string $content): void
    {
        $this->standard[] = $content;
    }

    public function writeError(string $content): void
    {
        $this->errors[] = $content;
    }

    public function standard(): string
    {
        return implode('', $this->standard);
    }

    public function error(): string
    {
        return implode('', $this->errors);
    }

    /** @return list<string> */
    public function standardChunks(): array
    {
        return $this->standard;
    }

    /** @return list<string> */
    public function errorChunks(): array
    {
        return $this->errors;
    }
}
