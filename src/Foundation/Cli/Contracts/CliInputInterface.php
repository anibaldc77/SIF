<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Contracts;

interface CliInputInterface
{
    /** @return list<string> */
    public function tokens(): array;

    /** @return array<string, string> */
    public function environment(): array;
}
