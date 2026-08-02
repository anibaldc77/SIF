<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Rendering;

use Sif\Foundation\Cli\Exceptions\CliConsoleException;

final readonly class CliOutputFormat
{
    public function __construct(private string $value)
    {
        if (!in_array($this->value, ['text', 'json'], true)) {
            throw new CliConsoleException(sprintf('Unsupported CLI output format "%s".', $this->value));
        }
    }

    public static function text(): self
    {
        return new self('text');
    }

    public static function json(): self
    {
        return new self('json');
    }

    public function value(): string
    {
        return $this->value;
    }
}
