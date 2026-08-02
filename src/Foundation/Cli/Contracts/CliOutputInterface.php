<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Contracts;

interface CliOutputInterface
{
    public function write(string $content): void;

    public function writeError(string $content): void;
}
