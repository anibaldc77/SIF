<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Output;

use Closure;
use Sif\Foundation\Cli\Contracts\CliOutputInterface;

final readonly class CallbackCliOutput implements CliOutputInterface
{
    /** @var Closure(string): void */
    private Closure $standardWriter;

    /** @var Closure(string): void */
    private Closure $errorWriter;

    /**
     * @param callable(string): void $standardWriter
     * @param callable(string): void $errorWriter
     */
    public function __construct(callable $standardWriter, callable $errorWriter)
    {
        $this->standardWriter = Closure::fromCallable($standardWriter);
        $this->errorWriter = Closure::fromCallable($errorWriter);
    }

    public function write(string $content): void
    {
        ($this->standardWriter)($content);
    }

    public function writeError(string $content): void
    {
        ($this->errorWriter)($content);
    }
}
