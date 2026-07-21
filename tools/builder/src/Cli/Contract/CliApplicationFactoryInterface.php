<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Contract;

interface CliApplicationFactoryInterface
{
    public function create(): CliApplicationInterface;
}
