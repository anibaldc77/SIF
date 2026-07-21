<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Contract;

interface PathResolverInterface
{
    public function resolve(string $path): string;
}
