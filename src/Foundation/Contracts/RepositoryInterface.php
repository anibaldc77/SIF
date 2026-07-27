<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Persistence\RepositoryName;

interface RepositoryInterface
{
    public function name(): RepositoryName;

    public function managedType(): string;
}
