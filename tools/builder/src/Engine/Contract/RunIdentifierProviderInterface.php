<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Contract;

interface RunIdentifierProviderInterface
{
    public function next(): string;
}
