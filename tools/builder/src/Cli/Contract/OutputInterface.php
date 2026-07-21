<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Contract;

interface OutputInterface
{
    public function write(string $content): void;

    public function writeError(string $content): void;
}
