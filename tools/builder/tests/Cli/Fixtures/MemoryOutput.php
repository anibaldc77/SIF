<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Fixtures;

use Sif\Builder\Cli\Contract\OutputInterface;

final class MemoryOutput implements OutputInterface
{
    public string $standardOutput = '';
    public string $standardError = '';

    public function write(string $content): void
    {
        $this->standardOutput .= $content;
    }

    public function writeError(string $content): void
    {
        $this->standardError .= $content;
    }
}
